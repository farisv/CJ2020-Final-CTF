const EMPTY_CELL = 'E';
const PLAYER_HUMAN = 'X';
const PLAYER_BOT = 'O';

function validate(state) {
    if (typeof state !== 'string' && !(state instanceof String)) {
        return false;
    }
    if (state.length !== 9) {
        return false;
    }
    let humanCount = botCount = emptyCount = 0;
    for (const c of state) {
        switch (c) {
            case EMPTY_CELL:
                emptyCount++;
                break;
            case PLAYER_HUMAN:
                humanCount++;
                break;
            case PLAYER_BOT:
                botCount++;
                break;
            default:
                return false;
        }
    }
    if (emptyCount === 0) {
        return false;
    }
    return Math.abs(humanCount - botCount) <= 1;
}

exports.solve = function(req, res) {
    const state = req.query.state;
    if (!state || !validate(state)) {
        return res.sendStatus(400);
    }

    availableMoves = [];
    for (let i = 0; i < state.length; i++) {
        if (state[i] === EMPTY_CELL) availableMoves.push(i);
    }
    const rand = Math.floor(Math.random() * availableMoves.length);
    return res.json(availableMoves[rand]);
}
