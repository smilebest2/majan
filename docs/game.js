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
  doraList: document.querySelector("#dora-list"),
  roundMark: document.querySelector("#round-mark"),
  roundSub: document.querySelector("#round-sub"),
  actionBar: document.querySelector("#action-bar"),
  winAction: document.querySelector("#win-action"),
  passAction: document.querySelector("#pass-action"),
  reachAction: document.querySelector("#reach-action"),
  northAction: document.querySelector("#north-action"),
  ponAction: document.querySelector("#pon-action"),
  kanAction: document.querySelector("#kan-action"),
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
  melds: [0, 1, 2].map((i) => document.querySelector(`#melds-${i}`)),
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
  hideActions();
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
    riichiDiscardIds: [null, null, null],
    riichiSticks: state.riichiSticks,
    declaringRiichi: false,
    nuki: [0, 0, 0],
    melds: [[], [], []],
    kanCount: 0,
    rinshan: [false, false, false],
    pendingCall: null,
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
  drawTile(game.dealer);
  render();
  beginTurn(game.dealer, true);
}

function drawTile(player, { rinshan = false } = {}) {
  if (!game.wall.length) return false;
  const tile = game.wall.pop();
  game.hands[player].push(tile);
  game.drawnIds[player] = tile.id;
  game.drawCounts[player] += 1;
  game.rinshan[player] = rinshan;
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
      renderHand();
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
    renderHand();
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
  game.rinshan[player] = false;
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
      game.pendingRon = { tile, discarder, score, resume: () => offerCalls(discarder, tile, 1) };
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
  offerCalls(discarder, tile, 1);
}

function matchingTiles(player, code) {
  const target = canonical(code);
  return game.hands[player].filter((tile) => canonical(tile.code) === target);
}

function availableCallOptions(player, tile) {
  if (game.riichi[player]) return { pon: false, kan: false };
  const count = matchingTiles(player, tile.code).length;
  return { pon: count >= 2, kan: count >= 3 && game.kanCount < 4 };
}

function offerCalls(discarder, tile, startStep) {
  for (let step = startStep; step <= 2; step += 1) {
    const player = (discarder + step) % 3;
    const options = availableCallOptions(player, tile);
    if (!options.pon && !options.kan) continue;

    if (player === 0) {
      game.pendingCall = { discarder, tile, options, nextStep: step + 1 };
      game.busy = false;
      hideActions();
      els.ponAction.hidden = !options.pon;
      els.kanAction.hidden = !options.kan;
      els.passAction.hidden = false;
      els.actionBar.hidden = false;
      setStatus(`${PLAYER_NAMES[discarder]}の${displayName(tile.code)}を鳴けます`);
      return;
    }

    const honor = canonical(tile.code)[1] === "z";
    if (options.kan && (honor || Math.random() < 0.35)) {
      performOpenCall(player, discarder, tile, "minkan");
      return;
    }
    if (options.pon && (honor || matchingTiles(player, tile.code).length >= 3 || Math.random() < 0.25)) {
      performOpenCall(player, discarder, tile, "pon");
      return;
    }
  }
  continueAfterDiscard(discarder);
}

function takeMatchingTiles(player, code, count) {
  const target = canonical(code);
  const taken = [];
  game.hands[player] = game.hands[player].filter((tile) => {
    if (taken.length < count && canonical(tile.code) === target) {
      taken.push(tile);
      return false;
    }
    return true;
  });
  return taken;
}

function removeCalledDiscard(discarder, tile) {
  const river = game.rivers[discarder];
  const index = river.findIndex((item) => item.id === tile.id);
  if (index >= 0) river.splice(index, 1);
}

function cancelIppatsu() {
  game.ippatsu = [false, false, false];
}

function performOpenCall(player, discarder, tile, type) {
  const needed = type === "pon" ? 2 : 3;
  const ownTiles = takeMatchingTiles(player, tile.code, needed);
  removeCalledDiscard(discarder, tile);
  game.melds[player].push({
    type,
    code: canonical(tile.code),
    tiles: [...ownTiles.map((item) => item.code), tile.code],
    from: discarder,
  });
  game.pendingCall = null;
  game.turn = player;
  game.drawnIds[player] = null;
  cancelIppatsu();
  hideActions();
  render();

  if (type === "minkan") {
    game.kanCount += 1;
    setStatus(`${PLAYER_NAMES[player]}が明槓しました`);
    if (!drawTile(player, { rinshan: true })) {
      finishDraw();
      return;
    }
    beginTurn(player);
    return;
  }

  setStatus(`${PLAYER_NAMES[player]}がポンしました`);
  if (player === 0) {
    game.busy = false;
    renderHand();
  } else {
    game.busy = true;
    timer = setTimeout(() => cpuDiscard(player), 420);
  }
}

function passCall() {
  const pending = game.pendingCall;
  game.pendingCall = null;
  hideActions();
  offerCalls(pending.discarder, pending.tile, pending.nextStep);
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
  const riichiCandidates = isClosedMelds(game.melds[player]) && !game.riichi[player] && game.points[player] >= 1000 ? getRiichiDiscards(hand, game.melds[player]) : [];
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
  if (game.justDeclaredRiichi[player]) game.riichiDiscardIds[player] = choice.id;
  setStatus(`${PLAYER_NAMES[player]}が${displayName(choice.code)}を打牌`);
  discard(player, choice.id);
}

function cpuNukiThenDiscard(player) {
  const kanOptions = getSelfKanOptions(player);
  if (kanOptions.length) {
    performSelfKan(player, kanOptions[0]);
    return;
  }
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

function getSelfKanOptions(player) {
  if (game.kanCount >= 4) return [];
  if (game.riichi[player]) {
    const drawn = game.hands[player].find((tile) => tile.id === game.drawnIds[player]);
    if (!drawn) return [];
    const code = canonical(drawn.code);
    return canRiichiAnkan(game.hands[player], game.melds[player], drawn.id, code)
      ? [{ type: "ankan", code }]
      : [];
  }
  const options = [];
  game.melds[player].forEach((meld, meldIndex) => {
    if (meld.type === "pon" && matchingTiles(player, meld.code).length) {
      options.push({ type: "kakan", code: meld.code, meldIndex });
    }
  });
  const counts = new Map();
  game.hands[player].forEach((tile) => counts.set(canonical(tile.code), (counts.get(canonical(tile.code)) || 0) + 1));
  for (const [code, count] of counts) {
    if (count === 4) options.push({ type: "ankan", code });
  }
  return options;
}

function performSelfKan(player, option = getSelfKanOptions(player)[0]) {
  if (!option || game.over) return;
  clearTimeout(timer);
  game.busy = true;
  hideActions();

  if (option.type === "kakan") {
    const tile = matchingTiles(player, option.code)[0];
    resolveChankan(player, tile, () => {
      takeMatchingTiles(player, option.code, 1);
      const meld = game.melds[player][option.meldIndex];
      meld.type = "kakan";
      meld.tiles.push(tile.code);
      completeKanDraw(player, "加槓");
    });
    return;
  }

  const tiles = takeMatchingTiles(player, option.code, 4);
  game.melds[player].push({ type: "ankan", code: option.code, tiles: tiles.map((tile) => tile.code), from: player });
  completeKanDraw(player, "暗槓");
}

function resolveChankan(kanner, tile, afterKan) {
  const candidates = [1, 2].map((step) => (kanner + step) % 3);
  for (const candidate of candidates) {
    const score = getScore(candidate, "ron", tile, { chankan: true });
    if (!score) continue;
    if (candidate === 0) {
      game.waitingRon = true;
      game.pendingRon = { tile, discarder: kanner, score, resume: afterKan };
      game.busy = false;
      els.winAction.textContent = "ロン";
      els.winAction.hidden = false;
      els.passAction.hidden = false;
      els.actionBar.hidden = false;
      setStatus(`${PLAYER_NAMES[kanner]}の加槓に槍槓できます`);
      return;
    }
    finishWin(candidate, kanner, "ron", [...game.hands[candidate], tile], score);
    return;
  }
  afterKan();
}

function completeKanDraw(player, label) {
  game.kanCount += 1;
  cancelIppatsu();
  setStatus(`${PLAYER_NAMES[player]}が${label}しました`);
  if (!drawTile(player, { rinshan: true })) {
    finishDraw();
    return;
  }
  render();
  beginTurn(player);
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

function isWinning(hand, melds = []) {
  return Scoring.isWinning(codesOf(hand), melds);
}

function isTenpai(hand, melds = []) {
  return Scoring.isTenpai(codesOf(hand), melds);
}

function isClosedMelds(melds) {
  return melds.every((meld) => meld.type === "ankan");
}

function getRiichiDiscards(hand, melds = []) {
  if (hand.length + melds.length * 3 !== 14) return [];
  return hand.filter((tile) => isTenpai(hand.filter((item) => item.id !== tile.id), melds)).map((tile) => tile.id);
}

function getWaitingCodes(hand, melds = []) {
  const codes = codesOf(hand);
  const counts = toCounts(hand);
  for (const meld of melds) {
    const meldCodes = meld.tiles || Array(meld.type === "pon" ? 3 : 4).fill(meld.code);
    meldCodes.forEach((code) => { counts[tileIndex(code)] += 1; });
  }
  return TILE_TYPES.filter((code, index) => counts[index] < 4 && Scoring.isWinning([...codes, code], melds));
}

function canRiichiAnkan(hand, melds, drawnId, code) {
  const target = canonical(code);
  const drawn = hand.find((tile) => tile.id === drawnId);
  if (!drawn || canonical(drawn.code) !== target) return false;
  if (hand.filter((tile) => canonical(tile.code) === target).length !== 4) return false;
  const before = getWaitingCodes(hand.filter((tile) => tile.id !== drawnId), melds);
  const afterHand = hand.filter((tile) => canonical(tile.code) !== target);
  const afterMelds = [...melds, { type: "ankan", code: target, tiles: [target, target, target, target] }];
  const after = getWaitingCodes(afterHand, afterMelds);
  return before.length > 0 && before.join("|") === after.join("|");
}

function seatWind(player) {
  return ((player - game.dealer + 3) % 3) + 1;
}

function getScore(player, winType, ronTile = null, extraContext = {}) {
  const hand = ronTile ? [...game.hands[player], ronTile] : game.hands[player];
  if (hand.length + game.melds[player].length * 3 !== 14) return null;
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
    doraIndicators: game.deadWall.slice(0, 1 + game.kanCount).map((tile) => tile.code),
    nuki: game.nuki[player],
    melds: game.melds[player],
    haitei: game.wall.length === 0,
    rinshan: game.rinshan[player],
    lastTile: lastTile.code,
    firstDraw: game.drawCounts[player] === 1,
    isDealer: player === game.dealer,
    ...extraContext,
  });
}

function refreshActions() {
  hideActions();
  if (game.over || game.turn !== 0 || game.busy) return;
  const canTsumo = Boolean(getScore(0, "tsumo"));
  const kanOptions = getSelfKanOptions(0);
  if (canTsumo) {
    els.winAction.textContent = "ツモ";
    els.winAction.hidden = false;
  }
  if (isClosedMelds(game.melds[0]) && !game.riichi[0] && game.points[0] >= 1000 && getRiichiDiscards(game.hands[0], game.melds[0]).length) {
    els.reachAction.hidden = false;
  }
  if (!game.riichi[0] && game.hands[0].some((tile) => canonical(tile.code) === "4z")) {
    els.northAction.hidden = false;
  }
  if (kanOptions.length) els.kanAction.hidden = false;
  if (game.riichi[0]) {
    if (canTsumo || kanOptions.length) {
      els.passAction.textContent = "ツモ切り";
      els.passAction.hidden = false;
    } else {
      scheduleRiichiAutoDiscard();
    }
  }
  els.actionBar.hidden = [els.winAction, els.reachAction, els.northAction, els.kanAction, els.passAction].every((el) => el.hidden);
}

function scheduleRiichiAutoDiscard() {
  clearTimeout(timer);
  const drawnId = game.drawnIds[0];
  if (!drawnId) return;
  setStatus("リーチ中：自動でツモ切りします");
  timer = setTimeout(() => autoDiscardRiichi(), 520);
}

function autoDiscardRiichi() {
  const drawnId = game.drawnIds[0];
  if (!drawnId || game.over || game.turn !== 0 || game.busy || !game.riichi[0]) return;
  clearTimeout(timer);
  hideActions();
  game.busy = true;
  renderHand();
  setStatus("リーチ中：ツモ切り");
  discard(0, drawnId);
}

function hideActions() {
  els.actionBar.hidden = true;
  els.winAction.hidden = true;
  els.reachAction.hidden = true;
  els.northAction.hidden = true;
  els.ponAction.hidden = true;
  els.kanAction.hidden = true;
  els.passAction.hidden = true;
  els.passAction.textContent = "見送る";
}

function winAction() {
  clearTimeout(timer);
  if (game.waitingRon && game.pendingRon) {
    const { tile, discarder, score } = game.pendingRon;
    finishWin(0, discarder, "ron", [...game.hands[0], tile], score);
    return;
  }
  const score = getScore(0, "tsumo");
  if (score) finishWin(0, null, "tsumo", [...game.hands[0]], score);
}

function passRon() {
  const { discarder, resume } = game.pendingRon;
  game.waitingRon = false;
  game.pendingRon = null;
  hideActions();
  if (resume) resume();
  else continueAfterDiscard(discarder);
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

  const pointsBefore = [...game.points];
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
  const scoreLine = score.yakuman
    ? `${score.limitName}　${totalWon.toLocaleString("ja-JP")}点獲得`
    : `${score.fu}符 ${score.han}翻${score.limitName ? ` ${score.limitName}` : ""}　${totalWon.toLocaleString("ja-JP")}点獲得`;
  showResult({
    title,
    mark: "和",
    detail: winType === "ron" ? `${PLAYER_NAMES[winner]}が${PLAYER_NAMES[loser]}から和了` : `${PLAYER_NAMES[winner]}のツモ和了`,
    handHtml: resultHandHtml(winner, hand, winType),
    yakuItems: score.yaku,
    scoreLine,
    pointDeltas: game.points.map((points, player) => points - pointsBefore[player]),
  });
  render();
}

function resultHandHtml(player, concealedHand, winType) {
  const winningId = winType === "tsumo" ? game.drawnIds[player] : concealedHand[concealedHand.length - 1]?.id;
  const concealed = [...concealedHand].sort(tileSort).map((tile) => {
    const winning = tile.id === winningId ? " result-winning" : "";
    return `<img class="${winning.trim()}" src="${tileSrc(tile.code)}" alt="${escapeHtml(displayName(tile.code))}" />`;
  }).join("");
  const melds = game.melds[player].map((meld) => {
    const codes = meld.tiles || Array(meld.type === "pon" ? 3 : 4).fill(meld.code);
    return `<span class="result-meld">${codes.map((code) => `<img src="${tileSrc(code)}" alt="${escapeHtml(displayName(code))}" />`).join("")}</span>`;
  }).join("");
  return `<span class="result-concealed">${concealed}</span>${melds}`;
}

function finishDraw() {
  if (game.over) return;
  game.over = true;
  game.busy = true;
  clearTimeout(timer);
  hideActions();

  const pointsBefore = [...game.points];
  const tenpai = game.hands.map((hand, player) => isTenpai(hand, game.melds[player]));
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
    handHtml: "",
    yakuItems: [{ name: readyCount > 0 && readyCount < 3 ? "流局聴牌料" : "点数移動なし", value: readyCount > 0 && readyCount < 3 ? "3,000点" : "" }],
    scoreLine: dealerRepeats ? "親が聴牌のため連荘" : "親流れ",
    pointDeltas: game.points.map((points, player) => points - pointsBefore[player]),
  });
  render();
}

function showResult({ title, mark, detail, handHtml, yakuItems, scoreLine, pointDeltas }) {
  els.resultEyebrow.textContent = game.matchOver ? "HALF GAME COMPLETE" : `${roundLabel()} RESULT`;
  els.resultTitle.textContent = title;
  els.resultDetail.textContent = detail;
  els.resultMark.textContent = mark;
  els.resultHand.innerHTML = handHtml || "";
  els.resultYaku.innerHTML = `<strong>${escapeHtml(scoreLine)}</strong><div class="result-yaku-list">${(yakuItems || []).map((item) => `<span><b>${escapeHtml(item.name)}</b><em>${escapeHtml(item.value ?? `${item.han}翻`)}</em></span>`).join("")}</div>`;
  els.resultScores.innerHTML = ranking().map((player, index) => {
    const delta = pointDeltas?.[player] || 0;
    const deltaLabel = `${delta > 0 ? "+" : ""}${delta.toLocaleString("ja-JP")}`;
    return `<div><b>${index + 1}位</b><span>${PLAYER_NAMES[player]}</span><small class="${delta > 0 ? "gain" : delta < 0 ? "loss" : ""}">${deltaLabel}</small><strong>${game.points[player].toLocaleString("ja-JP")}</strong></div>`;
  }).join("");
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
    els.rivers[player].innerHTML = river.map((tile, index) => {
      const classes = [index === river.length - 1 ? "river-last" : "", tile.id === game.riichiDiscardIds[player] ? "riichi-discard" : ""].filter(Boolean).join(" ");
      return tileImage(tile, displayName(tile.code), classes);
    }).join("");
  });
  els.wallCount.textContent = `残り ${game.wall.length}`;
  els.doraList.innerHTML = game.deadWall.slice(0, 1 + game.kanCount).map((tile) => tileImage(tile, `ドラ表示牌 ${displayName(tile.code)}`)).join("");
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
  renderMelds();
}

function renderMelds() {
  els.melds.forEach((container, player) => {
    container.innerHTML = game.melds[player].map((meld) => {
      const codes = meld.tiles || Array(meld.type === "pon" ? 3 : 4).fill(meld.code);
      const images = codes.map((code, index) => {
        const concealedEdge = meld.type === "ankan" && (index === 0 || index === codes.length - 1);
        const displayCode = concealedEdge ? "ura" : code;
        const called = meld.type !== "ankan" && index === codes.length - 1 ? " called" : "";
        return `<img class="${called.trim()}" src="${tileSrc(displayCode)}" alt="${concealedEdge ? "伏せ牌" : displayName(code)}" />`;
      }).join("");
      return `<span class="meld-set ${meld.type}" aria-label="${meld.type === "pon" ? "ポン" : "カン"}">${images}</span>`;
    }).join("");
  });
}

function renderHand() {
  const sorted = [...game.hands[0]].sort(tileSort);
  const candidates = game.declaringRiichi ? getRiichiDiscards(game.hands[0], game.melds[0]) : [];
  els.hand.innerHTML = sorted.map((tile) => {
    const drawn = tile.id === game.drawnIds[0] ? " drawn" : "";
    const candidate = candidates.includes(tile.id) ? " riichi-candidate" : "";
    const riichiLocked = game.riichi[0] && tile.id !== game.drawnIds[0];
    const invalidDeclaration = game.declaringRiichi && !candidates.includes(tile.id);
    const disabled = game.turn !== 0 || game.busy || game.over || riichiLocked || invalidDeclaration;
    return `<button class="tile-button${drawn}${candidate}" type="button" data-id="${tile.id}" aria-label="${displayName(tile.code)}を打つ" ${disabled ? "disabled" : ""}>${tileImage(tile, "")}</button>`;
  }).join("");
}

function tileImage(tile, alt, className = "") {
  return `<img${className ? ` class="${className}"` : ""} src="${tileSrc(tile.code)}" alt="${escapeHtml(alt)}" draggable="false" />`;
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
  value: Object.freeze({ isWinning, isTenpai, getRiichiDiscards, getWaitingCodes, canRiichiAnkan, advanceMatchState, canonical, tileIndex }),
  configurable: true,
});

els.hand.addEventListener("click", (event) => {
  const button = event.target.closest(".tile-button");
  if (!button || game.turn !== 0 || game.busy || game.over) return;
  if (game.declaringRiichi) {
    const valid = getRiichiDiscards(game.hands[0], game.melds[0]).includes(Number(button.dataset.id));
    if (!valid) return;
    game.declaringRiichi = false;
    game.riichi[0] = true;
    game.doubleRiichi[0] = game.rivers[0].length === 0;
    game.ippatsu[0] = true;
    game.justDeclaredRiichi[0] = true;
    game.riichiDiscardIds[0] = Number(button.dataset.id);
    game.points[0] -= 1000;
    game.riichiSticks += 1;
    setStatus("リーチ！");
  }
  clearTimeout(timer);
  hideActions();
  button.classList.add("selected");
  game.busy = true;
  setTimeout(() => discard(0, Number(button.dataset.id)), 100);
});

els.winAction.addEventListener("click", winAction);
els.passAction.addEventListener("click", () => {
  if (game.waitingRon) passRon();
  else if (game.pendingCall) passCall();
  else if (game.riichi[0]) autoDiscardRiichi();
});
els.reachAction.addEventListener("click", beginRiichi);
els.northAction.addEventListener("click", extractNorth);
els.ponAction.addEventListener("click", () => {
  if (!game.pendingCall) return;
  const { discarder, tile } = game.pendingCall;
  performOpenCall(0, discarder, tile, "pon");
});
els.kanAction.addEventListener("click", () => {
  if (game.pendingCall) {
    const { discarder, tile } = game.pendingCall;
    performOpenCall(0, discarder, tile, "minkan");
  } else {
    performSelfKan(0);
  }
});
document.querySelector("#new-game").addEventListener("click", () => {
  clearMatchState();
  startGame();
});
els.playAgain.addEventListener("click", () => {
  if (game.matchOver) clearMatchState();
  startGame(game.matchOver ? null : game.nextState);
});

startGame(loadMatchState());
