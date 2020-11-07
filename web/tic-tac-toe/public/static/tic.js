class Tic {
  static EMPTY_CELL = 'E';
  static EMPTY_STATE = Tic.EMPTY_CELL.repeat(3 * 3).split('');

  static PLAYER_HUMAN = 'X';
  static PLAYER_BOT = 'O';

  static PLAYER_HUMAN_COLOR = 'blue';
  static PLAYER_BOT_COLOR = 'red';

  static PATTERNS = [
    [0, 1, 2],
    [3, 4, 5],
    [6, 7, 8],
    [0, 3, 6],
    [1, 4, 7],
    [2, 5, 8],
    [0, 4, 8],
    [2, 4, 6]];

  constructor(state, turn) {
    this.state = state ? [...state] : [...Tic.EMPTY_STATE];
    this.turn = turn || Tic.PLAYER_HUMAN;
    this.onRefresh = (() => {});
    this.onGameDraw = (() => {});
    this.onGameWin = ((_) => {});
  }

  move(player, cell) {
    if (this.turn !== player || this.state[cell] !== Tic.EMPTY_CELL) {
      return false;
    }
    this.turn = player === Tic.PLAYER_HUMAN ? Tic.PLAYER_BOT : Tic.PLAYER_HUMAN;
    this.state[cell] = player;
    this.onRefresh();

    const winner = this.winner;
    if (winner) {
      this.turn = null;
      this.onGameWin(winner);
    } else if (this.isDraw) {
      this.turn = null;
      this.onGameDraw();
    }
    return true;
  }

  get isDraw() {
    let emptyCount = 0;
    for (const cell of this.state) {
      if (cell === Tic.EMPTY_CELL) ++emptyCount;
    }
    return emptyCount === 0;
  }

  get winner() {
    for (const pattern of Tic.PATTERNS) {
      let humanCount = 0;
      let botCount = 0;
      for (const i of pattern) {
        switch (this.state[i]) {
          case Tic.PLAYER_HUMAN:
            humanCount++;
            break
          case Tic.PLAYER_BOT:
            botCount++;
            break;
        }
      }
      if (humanCount === 3) return Tic.PLAYER_HUMAN;
      if (botCount === 3) return Tic.PLAYER_BOT;
    }
    return null;
  }

  reset() {
    this.turn = Tic.PLAYER_HUMAN;
    this.state = [...Tic.EMPTY_STATE];
    this.onRefresh();
  }
}
