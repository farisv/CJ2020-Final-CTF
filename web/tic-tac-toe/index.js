const path = require('path');
const express = require('express');
const { solve } = require('./src/solver');
const { report } = require('./src/reporter');

const app = new express();
const port = process.env.PORT || 3000;

const publicDir = path.join(__dirname, 'public');
const staticDir = path.join(publicDir, 'static');

app.use(express.json());
app.use('/static', express.static(staticDir));
app.get('/', (req, res) => {
    if (process.env.DEBUG) {
        console.info(req.url);
        console.info(req.headers);
    }
    res.sendFile('index.html', {root: publicDir});
});
app.get('/bot', solve);
app.post('/report', report);

app.listen(port, () => {
    console.log(`Listening at http://localhost:${port}`)
});
