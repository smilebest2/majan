const Scoring = globalThis.SanmaScoring;
if (!Scoring) throw new Error("Scoring engine was not loaded");

const SUIT_ORDER = { m: 0, p: 1, s: 2, z: 3 };
const TILE_TYPES = Scoring.TYPES;
const PLAYER_NAMES = ["あなた", "CPU 青", "CPU 橙"];
const WIND_NAMES = ["東", "南", "西", "北"];
const STORAGE_KEY = "sanma-half-game-v1";

const els = {
  hand: document.querySelector("#hand"),
  rivers: [0, 1, 2].map((i) => document.querySelector(`#river-${i}`)),
  wallCount: document.querySelector("#wall-count"),
  status: document.querySelector("#status"),
  dora: document.querySelector("#dora"),
  roundMark: document.querySelector("#round-mark"),
  roundSub: document.querySelector("#round-sub"),
  actionBar: document.querySelector("#action-bar"),
  winAction: document.querySelector("#win-action"),
  passAction: document.querySelector("#pass-action"),
  reachAction: document.querySelector("#reach-action"),
  northAction: document.querySelector("#north-action"),
  dialog: document.querySelector("#result-dialog"),
  resultEyebrow: document.querySelector("#result-eyebrow"),
  resultTitle: document.querySelector("#result-title"),
  resultDetail: document.querySelector("#result-detail"),
  resultMark: document.querySelector("#result-mark"),
  resultHand: document.querySelector("#result-hand"),
  resultYaku: document.querySelector("#result-yaku"),
  resultScores: document.querySelector("#result-scores"),
  playAgain: document.querySelector("#play-again"),
  cpuCounts: [document.querySelector("#cpu1-count"), document.querySelector("#cpu2-count")],
  scores: [0, 1, 2].map((i) => document.querySelector(`#score-${i}`)),
  winds: [0, 1, 2].map((i) => document.querySelector(`#wind-${i}`)),
  nukiCounts: [0, 1, 2].map((i) => document.querySelector(`#nuki-${i}`)),
  riichiSticks: document.querySelector("#riichi-sticks"),
};

let game;
let timer;

function range(from, to, suit) {
  return Array.from({ length: to - from + 1 }, (_, i) => `${from + i}${suit}`);
}

function canonical(code) {
  return Scoring.canonical(code);
}

function tileIndex(code) {
  return Scoring.tileIndex(code);
}

function tileSort(a, b) {
  const suitDiff = SUIT_ORDER[a.code[1]] - SUIT_ORDER[b.code[1]];
  if (suitDiff) return suitDiff;
  const an = Number(a.code[0]) || 5;
  const bn = Number(b.code[0]) || 5;
  return an - bn || Number(a.code[0] !== "0") - Number(b.code[0] !== "0") || a.id - b.id;
}

function buildWall() {
  let id = 0;
  const wall = [];
  for (const type of TILE_TYPES) {
    for (let copy = 0; copy < 4; copy += 1) {
      let code = type;
      if (copy === 0 && (type === "5p" || type === "5s")) code = `0${type[1]}`;
      wall.push({ id: id += 1, code });
    }
  }
  for (let i = wall.length - 1; i > 0; i -= 1) {
    const j = Math.floor(Math.random() * (i + 1));
    [wall[i], wall[j]] = [wall[j], wall[i]];
  }
  return wall;
}

function defaultMatchState() {
  return {
    points: [35000, 35000, 35000],
    dealer: 0,
    roundWind: 0,
    handNumber: 0,
    honba: 0,
    riichiSticks: 0,
  };
}

function saveMatchState(state) {
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch (_) { /* Device storage may be unavailable. */ }
}

function loadMatchState() {
  try {
    const saved = JSON.parse(localStorage.getItem(STORAGE_KEY));
    if (!saved || !Array.isArray(saved.points) || saved.points.length !== 3) return null;
    return saved;
  } catch (_) {
    return null;
  }
}

function clearMatchState() {
  try { localStorage.removeItem(STORAGE_KEY); } catch (_) { /* Ignore storage errors. */ }
}

function startGame(matchState = null) {
  clearTimeout(timer);
  if (els.dialog.open) els.dialog.close();
  const state = matchState || defaultMatchState();
  const fullWall = buildWall();
  const deadWall = fullWall.splice(-14);
  game = {
    wall: fullWall,
    deadWall,
    hands: [[], [], []],
    rivers: [[], [], []],
    turn: state.dealer,
    drawnIds: [null, null, null],
    drawCounts: [0, 0, 0],
    waitingRon: false,
    pendingRon: null,
    over: false,
    busy: true,
    points: [...state.points],
    dealer: state.dealer,
    roundWind: state.roundWind,
    handNumber: state.handNumber,
    honba: state.honba,
    riichi: [false, false, false],
    doubleRiichi: [false, false, false],
    ippatsu: [false, false, false],
    justDeclaredRiichi: [false, false, false],
    riichiSticks: state.riichiSticks,
    declaringRiichi: false,
    nuki: [0, 0, 0],
    matchOver: false,
    nextState: null,
  };
  saveMatchState({
    points: game.points,
    dealer: game.dealer,
    roundWind: game.roundWind,
    handNumber: game.handNumber,
    honba: game.honba,
    riichiSticks: game.riichiSticks,
  });

  for (let deal = 0; deal < 13; deal += 1) {
    for (let player = 0; player < 3; player += 1) game.hands[player].push(game.wall.pop());
  }
  els.dora.src = tileSrc(deadWall[0].code);
  els.dora.alt = `ドラ表示牌 ${displayName(deadWall[0].code)}`;
  drawTile(game.dealer);
  render();
  beginTurn(game.dealer, true);
}

function drawTile(player) {
  if (!game.wall.length) return false;
  const tile = game.wall.pop();
  game.hands[player].push(tile);
  game.drawnIds[player] = tile.id;
  game.drawCounts[player] += 1;
  game.turn = player;
  return true;
}

function beginTurn(player, firstTurn = false) {
  if (game.over) return;
  render();
  const score = getScore(player, "tsumo");
  if (score) {
    if (player === 0) {
      game.busy = false;
      setStatus("ツモ和了できます");
      refreshActions();
    } else {
      setStatus(`${PLAYER_NAMES[player]}が和了しました`);
      timer = setTimeout(() => finishWin(player, null, "tsumo", [...game.hands[player]], score), firstTurn ? 700 : 450);
    }
    return;
  }

  if (player === 0) {
    game.busy = false;
    setStatus("あなたの番です。牌を選んでください");
    refreshActions();
  } else {
    game.busy = true;
    setStatus(`${PLAYER_NAMES[player]}が考えています…`);
    timer = setTimeout(() => cpuNukiThenDiscard(player), firstTurn ? 700 : 450);
  }
}

function discard(player, tileId) {
  const hand = game.hands[player];
  const index = hand.findIndex((tile) => tile.id === tileId);
  if (index < 0) return;
  const [tile] = hand.splice(index, 1);
  game.rivers[player].push(tile);
  game.drawnIds[player] = null;
  if (game.justDeclaredRiichi[player]) game.justDeclaredRiichi[player] = false;
  else if (game.ippatsu[player]) game.ippatsu[player] = false;
  render();
  vibrate(18);
  resolveDiscard(player, tile);
}

function resolveDiscard(discarder, tile) {
  const candidates = [1, 2].map((step) => (discarder + step) % 3);
  for (const candidate of candidates) {
    const score = getScore(candidate, "ron", tile);
    if (!score) continue;
    if (candidate === 0) {
      game.waitingRon = true;
      game.pendingRon = { tile, discarder, score };
      game.busy = false;
      els.winAction.textContent = "ロン";
      els.winAction.hidden = false;
      els.passAction.hidden = false;
      els.actionBar.hidden = false;
      setStatus(`${PLAYER_NAMES[discarder]}の打牌にロンできます`);
      renderHand();
      return;
    }
    finishWin(candidate, discarder, "ron", [...game.hands[candidate], tile], score);
    return;
  }
  continueAfterDiscard(discarder);
}

function continueAfterDiscard(discarder) {
  const next = (discarder + 1) % 3;
  game.busy = true;
  hideActions();
  setStatus(next === 0 ? "あなたのツモです" : `${PLAYER_NAMES[next]}がツモします`);
  timer = setTimeout(() => {
    if (!drawTile(next)) {
      finishDraw();
      return;
    }
    beginTurn(next);
  }, next === 0 ? 260 : 390);
}

function cpuDiscard(player) {
  if (game.over) return;
  const hand = game.hands[player];
  let pool = hand;
  const riichiCandidates = !game.riichi[player] && game.points[player] >= 1000 ? getRiichiDiscards(hand) : [];
  if (riichiCandidates.length) {
    game.riichi[player] = true;
    game.doubleRiichi[player] = game.rivers[player].length === 0;
    game.ippatsu[player] = true;
    game.justDeclaredRiichi[player] = true;
    game.points[player] -= 1000;
    game.riichiSticks += 1;
    pool = hand.filter((tile) => riichiCandidates.includes(tile.id));
    setStatus(`${PLAYER_NAMES[player]}がリーチ！`);
    render();
  }
  if (game.riichi[player] && game.drawnIds[player]) {
    pool = hand.filter((tile) => tile.id === game.drawnIds[player]);
  }

  let bestScore = -Infinity;
  let choices = [];
  for (const tile of pool) {
    const remaining = hand.filter((item) => item.id !== tile.id);
    const score = handPotential(remaining);
    if (score > bestScore) {
      bestScore = score;
      choices = [tile];
    } else if (score === bestScore) choices.push(tile);
  }
  const choice = choices[Math.floor(Math.random() * choices.length)];
  setStatus(`${PLAYER_NAMES[player]}が${displayName(choice.code)}を打牌`);
  discard(player, choice.id);
}

function cpuNukiThenDiscard(player) {
  const north = game.hands[player].find((tile) => canonical(tile.code) === "4z");
  if (!north || game.riichi[player]) {
    cpuDiscard(player);
    return;
  }
  game.hands[player] = game.hands[player].filter((tile) => tile.id !== north.id);
  game.nuki[player] += 1;
  game.ippatsu = [false, false, false];
  setStatus(`${PLAYER_NAMES[player]}が北を抜きました`);
  if (!drawTile(player)) {
    finishDraw();
    return;
  }
  render();
  const score = getScore(player, "tsumo");
  if (score) {
    timer = setTimeout(() => finishWin(player, null, "tsumo", [...game.hands[player]], score), 350);
    return;
  }
  timer = setTimeout(() => cpuNukiThenDiscard(player), 280);
}

function handPotential(hand) {
  const counts = toCounts(hand);
  let score = 0;
  counts.forEach((count) => {
    if (count >= 2) score += 3;
    if (count >= 3) score += 5;
  });
  for (const suit of ["p", "s"]) {
    for (let n = 1; n <= 8; n += 1) {
      if (counts[tileIndex(`${n}${suit}`)] && counts[tileIndex(`${n + 1}${suit}`)]) score += 2;
    }
    for (let n = 1; n <= 7; n += 1) {
      if (counts[tileIndex(`${n}${suit}`)] && counts[tileIndex(`${n + 2}${suit}`)]) score += 1;
    }
  }
  return score;
}

function toCounts(hand) {
  const counts = Array(TILE_TYPES.length).fill(0);
  for (const tile of hand) counts[tileIndex(tile.code)] += 1;
  return counts;
}

function codesOf(hand) {
  return hand.map((tile) => tile.code);
}

function isWinning(hand) {
  return Scoring.isWinning(codesOf(hand));
}

function isTenpai(hand) {
  return Scoring.isTenpai(codesOf(hand));
}

function getRiichiDiscards(hand) {
  if (hand.length % 3 !== 2) return [];
  return hand.filter((tile) => isTenpai(hand.filter((item) => item.id !== tile.id))).map((tile) => tile.id);
}

function seatWind(player) {
  return ((player - game.dealer + 3) % 3) + 1;
}

function getScore(player, winType, ronTile = null) {
  const hand = ronTile ? [...game.hands[player], ronTile] : game.hands[player];
  if (hand.length !== 14) return null;
  const drawnTile = hand.find((tile) => tile.id === game.drawnIds[player]);
  const lastTile = ronTile || drawnTile || hand[hand.length - 1];
  return Scoring.scoreHand(codesOf(hand), {
    winType,
    riichi: game.riichi[player],
    doubleRiichi: game.doubleRiichi[player],
    ippatsu: game.ippatsu[player],
    seatWind: seatWind(player),
    roundWind: game.roundWind + 1,
    doraIndicator: game.deadWall[0].code,
    nuki: game.nuki[player],
    haitei: game.wall.length === 0,
    lastTile: lastTile.code,
    firstDraw: game.drawCounts[player] === 1,
    isDealer: player === game.dealer,
  });
}

function refreshActions() {
  hideActions();
  if (game.over || game.turn !== 0 || game.busy) return;
  if (getScore(0, "tsumo")) {
    els.winAction.textContent = "ツモ";
    els.winAction.hidden = false;
  }
  if (!game.riichi[0] && game.points[0] >= 1000 && getRiichiDiscards(game.hands[0]).length) {
    els.reachAction.hidden = false;
  }
  if (!game.riichi[0] && game.hands[0].some((tile) => canonical(tile.code) === "4z")) {
    els.northAction.hidden = false;
  }
  els.actionBar.hidden = [els.winAction, els.reachAction, els.northAction].every((el) => el.hidden);
}

function hideActions() {
  els.actionBar.hidden = true;
  els.winAction.hidden = true;
  els.reachAction.hidden = true;
  els.northAction.hidden = true;
  els.passAction.hidden = true;
}

function winAction() {
  if (game.waitingRon && game.pendingRon) {
    const { tile, discarder, score } = game.pendingRon;
    finishWin(0, discarder, "ron", [...game.hands[0], tile], score);
    return;
  }
  const score = getScore(0, "tsumo");
  if (score) finishWin(0, null, "tsumo", [...game.hands[0]], score);
}

function passRon() {
  const discarder = game.pendingRon.discarder;
  game.waitingRon = false;
  game.pendingRon = null;
  hideActions();
  continueAfterDiscard(discarder);
}

function beginRiichi() {
  game.declaringRiichi = true;
  hideActions();
  setStatus("リーチ宣言牌を選んでください");
  renderHand();
}

function extractNorth() {
  if (game.turn !== 0 || game.busy || game.over || game.riichi[0]) return;
  const north = game.hands[0].find((tile) => canonical(tile.code) === "4z");
  if (!north) return;
  game.busy = true;
  game.hands[0] = game.hands[0].filter((tile) => tile.id !== north.id);
  game.nuki[0] += 1;
  game.ippatsu = [false, false, false];
  setStatus("北を抜きました。補充牌をツモします");
  vibrate(25);
  timer = setTimeout(() => {
    if (!drawTile(0)) {
      finishDraw();
      return;
    }
    game.busy = false;
    render();
    refreshActions();
  }, 260);
}

function applyTransfers(transfers) {
  transfers.forEach((amount, player) => { game.points[player] += amount; });
}

function advanceMatchState(state, dealerRepeats, wasDraw) {
  let { dealer, roundWind, handNumber } = state;
  const honba = dealerRepeats || wasDraw ? state.honba + 1 : 0;
  if (!dealerRepeats) {
    dealer = (dealer + 1) % 3;
    handNumber += 1;
    if (handNumber >= 3) {
      handNumber = 0;
      roundWind += 1;
    }
  }
  return {
    points: [...state.points],
    dealer,
    roundWind,
    handNumber,
    honba,
    riichiSticks: state.riichiSticks,
  };
}

function nextMatchState(dealerRepeats, wasDraw) {
  return advanceMatchState(game, dealerRepeats, wasDraw);
}

function shouldEndMatch(nextState, dealerRepeats, winner = null) {
  if (game.points.some((points) => points < 0)) return true;
  const top = Math.max(...game.points);
  const finalSouth = game.roundWind === 1 && game.handNumber === 2;
  if (finalSouth && dealerRepeats && winner === game.dealer && game.points[winner] === top) return true;
  if (nextState.roundWind === 2 && top >= 40000) return true;
  if (game.roundWind >= 2 && top >= 40000 && !dealerRepeats) return true;
  return nextState.roundWind > 2;
}

function finishWin(winner, loser, winType, hand, score) {
  if (game.over) return;
  game.over = true;
  game.busy = true;
  clearTimeout(timer);
  hideActions();

  const payment = Scoring.calculatePayments(score, {
    winType,
    winner,
    loser,
    dealer: game.dealer,
    honba: game.honba,
  });
  applyTransfers(payment.transfers);
  const pot = game.riichiSticks * 1000;
  game.points[winner] += pot;
  game.riichiSticks = 0;

  const dealerRepeats = winner === game.dealer;
  const nextState = nextMatchState(dealerRepeats, false);
  game.matchOver = shouldEndMatch(nextState, dealerRepeats, winner);
  game.nextState = nextState;
  if (game.matchOver) clearMatchState();
  else saveMatchState(nextState);

  const title = winType === "tsumo" ? "ツモ！" : "ロン！";
  const totalWon = payment.transfers[winner] + pot;
  const yakuLine = score.yaku.map((item) => `${item.name} ${item.han}翻`).join(" · ");
  const scoreLine = score.yakuman
    ? `${score.limitName}　${totalWon.toLocaleString("ja-JP")}点獲得`
    : `${score.fu}符 ${score.han}翻${score.limitName ? ` ${score.limitName}` : ""}　${totalWon.toLocaleString("ja-JP")}点獲得`;
  showResult({
    title,
    mark: "和",
    detail: `${PLAYER_NAMES[winner]}の和了`,
    hand,
    yakuLine,
    scoreLine,
  });
  render();
}

function finishDraw() {
  if (game.over) return;
  game.over = true;
  game.busy = true;
  clearTimeout(timer);
  hideActions();

  const tenpai = game.hands.map((hand) => isTenpai(hand));
  const readyCount = tenpai.filter(Boolean).length;
  if (readyCount > 0 && readyCount < 3) {
    const gain = 3000 / readyCount;
    const loss = 3000 / (3 - readyCount);
    tenpai.forEach((ready, player) => { game.points[player] += ready ? gain : -loss; });
  }
  const dealerRepeats = tenpai[game.dealer];
  const nextState = nextMatchState(dealerRepeats, true);
  game.matchOver = shouldEndMatch(nextState, dealerRepeats, null);
  game.nextState = nextState;
  if (game.matchOver) clearMatchState();
  else saveMatchState(nextState);
  const tenpaiNames = tenpai.map((ready, player) => ready ? PLAYER_NAMES[player] : null).filter(Boolean);
  showResult({
    title: "流局",
    mark: "流",
    detail: tenpaiNames.length ? `聴牌：${tenpaiNames.join("、")}` : "全員ノーテン",
    hand: null,
    yakuLine: readyCount > 0 && readyCount < 3 ? "流局聴牌料 3,000点" : "点数移動なし",
    scoreLine: dealerRepeats ? "親が聴牌のため連荘" : "親流れ",
  });
  render();
}

function showResult({ title, mark, detail, hand, yakuLine, scoreLine }) {
  els.resultEyebrow.textContent = game.matchOver ? "HALF GAME COMPLETE" : `${roundLabel()} RESULT`;
  els.resultTitle.textContent = title;
  els.resultDetail.textContent = detail;
  els.resultMark.textContent = mark;
  els.resultHand.innerHTML = hand ? [...hand].sort(tileSort).map((tile) => tileImage(tile, "")).join("") : "";
  els.resultYaku.innerHTML = `<strong>${escapeHtml(scoreLine)}</strong><span>${escapeHtml(yakuLine)}</span>`;
  els.resultScores.innerHTML = ranking().map((player, index) => `<div><b>${index + 1}位</b><span>${PLAYER_NAMES[player]}</span><strong>${game.points[player].toLocaleString("ja-JP")}</strong></div>`).join("");
  els.playAgain.textContent = game.matchOver ? "新しい半荘" : "次の局";
  renderHand();
  vibrate([40, 30, 80]);
  setTimeout(() => els.dialog.showModal(), 180);
}

function ranking() {
  return [0, 1, 2].sort((a, b) => game.points[b] - game.points[a] || a - b);
}

function roundLabel() {
  return `${WIND_NAMES[game.roundWind] || "西"}${["一", "二", "三"][game.handNumber]}局`;
}

function render() {
  renderHand();
  game.rivers.forEach((river, player) => {
    els.rivers[player].innerHTML = river.map((tile) => tileImage(tile, displayName(tile.code))).join("");
  });
  els.wallCount.textContent = `残り ${game.wall.length}`;
  els.roundMark.textContent = roundLabel();
  els.roundSub.textContent = `${game.honba}本場`;
  els.cpuCounts.forEach((el, index) => { el.textContent = game.hands[index + 1].length; });
  els.scores.forEach((el, index) => { el.textContent = game.points[index].toLocaleString("ja-JP"); });
  els.winds.forEach((el, player) => {
    el.textContent = WIND_NAMES[seatWind(player) - 1];
    el.classList.toggle("dealer", player === game.dealer);
  });
  els.nukiCounts.forEach((el, index) => {
    el.textContent = `北×${game.nuki[index]}`;
    el.hidden = game.nuki[index] === 0;
  });
  els.riichiSticks.textContent = `供託 ${game.riichiSticks}`;
}

function renderHand() {
  const sorted = [...game.hands[0]].sort(tileSort);
  const candidates = game.declaringRiichi ? getRiichiDiscards(game.hands[0]) : [];
  els.hand.innerHTML = sorted.map((tile) => {
    const drawn = tile.id === game.drawnIds[0] ? " drawn" : "";
    const candidate = candidates.includes(tile.id) ? " riichi-candidate" : "";
    const riichiLocked = game.riichi[0] && tile.id !== game.drawnIds[0];
    const invalidDeclaration = game.declaringRiichi && !candidates.includes(tile.id);
    const disabled = game.turn !== 0 || game.busy || game.over || riichiLocked || invalidDeclaration;
    return `<button class="tile-button${drawn}${candidate}" type="button" data-id="${tile.id}" aria-label="${displayName(tile.code)}を打つ" ${disabled ? "disabled" : ""}>${tileImage(tile, "")}</button>`;
  }).join("");
}

function tileImage(tile, alt) {
  return `<img src="${tileSrc(tile.code)}" alt="${escapeHtml(alt)}" draggable="false" />`;
}

function tileSrc(code) {
  return `./img/hai/${code}.png`;
}

function displayName(code) {
  const numberNames = ["赤五", "一", "二", "三", "四", "五", "六", "七", "八", "九"];
  if (code[1] === "z") return ["", "東", "南", "西", "北", "白", "發", "中"][Number(code[0])];
  return `${numberNames[Number(code[0])]}${{ m: "萬", p: "筒", s: "索" }[code[1]]}`;
}

function escapeHtml(value) {
  return String(value).replace(/[&<>"]/g, (char) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", "\"": "&quot;" }[char]));
}

function setStatus(message) {
  els.status.textContent = message;
}

function vibrate(pattern) {
  if (navigator.vibrate) navigator.vibrate(pattern);
}

Object.defineProperty(globalThis, "__sanmaEngine", {
  value: Object.freeze({ isWinning, isTenpai, getRiichiDiscards, advanceMatchState, canonical, tileIndex }),
  configurable: true,
});

els.hand.addEventListener("click", (event) => {
  const button = event.target.closest(".tile-button");
  if (!button || game.turn !== 0 || game.busy || game.over) return;
  if (game.declaringRiichi) {
    const valid = getRiichiDiscards(game.hands[0]).includes(Number(button.dataset.id));
    if (!valid) return;
    game.declaringRiichi = false;
    game.riichi[0] = true;
    game.doubleRiichi[0] = game.rivers[0].length === 0;
    game.ippatsu[0] = true;
    game.justDeclaredRiichi[0] = true;
    game.points[0] -= 1000;
    game.riichiSticks += 1;
    setStatus("リーチ！");
  }
  button.classList.add("selected");
  game.busy = true;
  setTimeout(() => discard(0, Number(button.dataset.id)), 100);
});

els.winAction.addEventListener("click", winAction);
els.passAction.addEventListener("click", passRon);
els.reachAction.addEventListener("click", beginRiichi);
els.northAction.addEventListener("click", extractNorth);
document.querySelector("#new-game").addEventListener("click", () => {
  clearMatchState();
  startGame();
});
els.playAgain.addEventListener("click", () => {
  if (game.matchOver) clearMatchState();
  startGame(game.matchOver ? null : game.nextState);
});

startGame(loadMatchState());
