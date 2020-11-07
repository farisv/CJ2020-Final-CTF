const amqp = require('amqplib');

const queue = 'reports';
let channel = null;

async function connect() {
  if (channel) return channel;
  const connection = await amqp.connect('amqp://rabbitmq');
  channel = await connection.createChannel();
  channel.assertQueue(queue, { durable: true });
  return channel;
}

function validate(board) {
  if (typeof board !== 'string' && !(board instanceof String)) {
    return false;
  }
  return board.startsWith('?');
}

exports.report = async function(req, res) {
  const board = req.body.board || '';
  if (!validate(board)) {
    res.sendStatus(400);
    return;
  }

  try {
    // will queue the report so my AI can learn from it later
    const chan = await connect();
    chan.sendToQueue(queue, Buffer.from(board), { persistent: true });
    res.sendStatus(200);
  } catch (err) {
    console.error(err);
    res.sendStatus(500);
  }
}
