const puppeteer = require('puppeteer');
const amqp = require('amqplib');

const baseUrl = process.env.BASE_URL;
const flag = process.env.FLAG;
const queue = 'reports';

let connection = null;
let channel = null;

async function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function worker(message) {
    const board = message.content.toString();
    const url = `${baseUrl}${board}`;
    console.info(`visiting ${url}`);

    let browser = null;
    try {
        browser = await puppeteer.launch({
            headless: true,
            executablePath: '/usr/bin/chromium-browser',
            args: ['--no-sandbox', '--headless', '--disable-gpu', '--disable-dev-shm-usage'],
        });

        const page = await browser.newPage();
        await page.setCookie({ name: 'flag', value: flag, url: baseUrl });
        await page.goto(url, { timeout: 3000, waitUntil: 'networkidle0' });
    } catch (err) {
        console.error(`${err} while visiting ${url}`);
    } finally {
        if (browser) await browser.close();
    }
}

async function setup() {
    const N = 10;
    for (let i = 0; i < N; i++) {
        try {
            connection = await amqp.connect('amqp://rabbitmq');
            channel = await connection.createChannel();
            break;
        } catch (err) {
            if (i === N - 1) {
                throw err;
            }
            const sec = 2 ** i;
            console.info(`waiting rabbitmq, sleeping for ${sec} secs...`);
            await sleep(sec * 1000);
        }
    }

    await channel.assertQueue(queue, { durable: true });
    await channel.prefetch(4);
    await channel.consume(queue, async function (message) {
        await worker(message);
        await channel.ack(message);
    });
}

async function teardown() {
    if (channel) {
        await channel.close();
        channel = null;
    }
    if (connection) {
        await connection.close();
        channel = null;
    }
    process.exit(0);
}

process.on('SIGHUP', teardown);
process.on('SIGINT', teardown);
process.on('SIGTERM', teardown);

setup();
