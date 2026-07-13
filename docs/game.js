const SUIT_ORDER = { m: 0, p: 1, s: 2, z: 3 };
const TILE_TYPES = ["1m", "9m", ...range(1, 9, "p"), ...range(1, 9, "s"), ...range(1, 7, "z")];

const els = {
  hand: document.querySelector("#hand"),
  rivers: [0, 1, 2].map((i) => document.querySelector(`#river-${i}`)),
  wallCount: document.querySelector("#wall-count"),
  status: document.querySelector("#status"),
  dora: document.querySelector("#dora"),
  actionBar: document.querySelector("#action-bar"),
  winAction: document.querySelector("#win-action"),
  passAction: document.querySelector("#pass-action"),
  reachAction: document.querySelector("#reach-action"),
  northAction: document.querySelector("#north-action"),
  dialog: document.querySelector("#result-dialog"),
  resultTitle: document.querySelector("#result-title"),
  resultDetail: document.querySelector("#result-detail"),
  resultMark: document.querySelector("#result-mark"),
  resultHand: document.querySelector("#result-hand"),
  cpuCounts: [document.querySelector("#cpu1-count"), document.querySelector("#cpu2-count")],
  scores: [0, 1, 2].map((i) => document.querySelector(`#score-${i}`)),
  nukiCounts: [0, 1, 2].map((i) => document.querySelector(`#nuki-${i}`)),
  riichiSticks: document.querySelector("#riichi-sticks"),
};

let game;
let timer;

function range(from, to, suit) {
  return Array.from({ length: to - from + 1 }, (_, i) => `${from + i}${suit}`);
}

function canonical(code) {
  return code[0] === "0" ? `5${code[1]}` : code;
}

function tileIndex(code) {
  return TILE_TYPES.indexOf(canonical(code));
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

function startGame() {
  clearTimeout(timer);
  if (els.dialog.open) els.dialog.close();
  const fullWall = buildWall();
  const deadWall = fullWall.splice(-14);
  game = {
    wall: fullWall,
    deadWall,
    hands: [[], [], []],
    rivers: [[], [], []],
    turn: 0,
    drawnId: null,
    waitingRon: false,
    pendingRonTile: null,
    over: false,
    busy: false,
    points: [35000, 35000, 35000],
    riichi: [false, false, false],
    riichiSticks: 0,
    declaringRiichi: false,
    nuki: [0, 0, 0],
  };

  for (let round = 0; round < 13; round += 1) {
    for (let player = 0; player < 3; player += 1) game.hands[player].push(game.wall.pop());
  }
  els.dora.src = tileSrc(deadWall[0].code);
  els.dora.alt = `ドラ表示牌 ${deadWall[0].code}`;
  drawTile(0);
  setStatus("あなたの番です。牌を選んでください");
  render();
  refreshActions();
}

function drawTile(player) {
  if (!game.wall.length) {
    finishGame("流局", "山の牌がなくなりました", null, "流");
    return false;
  }
  const tile = game.wall.pop();
  game.hands[player].push(tile);
  game.drawnId = player === 0 ? tile.id : null;
  game.turn = player;
  return true;
}

function discard(player, tileId) {
  const hand = game.hands[player];
  const index = hand.findIndex((tile) => tile.id === tileId);
  if (index < 0) return;
  const [tile] = hand.splice(index, 1);
  game.rivers[player].push(tile);
  game.drawnId = null;
  render();
  vibrate(18);
  resolveDiscard(player, tile);
}

function resolveDiscard(player, tile) {
  const candidates = [1, 2].map((step) => (player + step) % 3);
  for (const candidate of candidates) {
    if (!isWinning([...game.hands[candidate], tile])) continue;
    if (candidate === 0) {
      game.waitingRon = true;
      game.pendingRonTile = tile;
      game.busy = false;
      els.winAction.textContent = "ロン";
      els.winAction.hidden = false;
      els.passAction.hidden = false;
      els.actionBar.hidden = false;
      setStatus(`${player === 1 ? "CPU 青" : "CPU 橙"}の打牌にロンできます`);
      renderHand();
      return;
    }
    finishGame("ロン", `${candidate === 1 ? "CPU 青" : "CPU 橙"}の和了です`, [...game.hands[candidate], tile], "放");
    return;
  }
  continueAfterDiscard(player);
}

function continueAfterDiscard(player) {
  const next = (player + 1) % 3;
  game.busy = true;
  hideActions();
  setStatus(next === 0 ? "あなたのツモです" : `${next === 1 ? "CPU 青" : "CPU 橙"}が考えています…`);
  timer = setTimeout(() => {
    if (!drawTile(next) || game.over) return;
    render();
    if (isWinning(game.hands[next])) {
      if (next === 0) {
        game.busy = false;
        setStatus("ツモ和了できます");
        refreshActions();
      } else {
        timer = setTimeout(() => finishGame("ツモ", `${next === 1 ? "CPU 青" : "CPU 橙"}のツモ和了です`, game.hands[next], "和"), 500);
      }
      return;
    }
    if (next === 0) {
      game.busy = false;
      setStatus("あなたの番です。牌を選んでください");
      renderHand();
      return;
    }
    timer = setTimeout(() => cpuNukiThenDiscard(next), 500);
  }, next === 0 ? 280 : 430);
}

function cpuDiscard(player) {
  if (game.over) return;
  const hand = game.hands[player];
  let bestScore = -Infinity;
  let candidates = [];
  let pool = hand;
  const riichiCandidates = !game.riichi[player] ? getRiichiDiscards(hand) : [];
  if (riichiCandidates.length) {
    game.riichi[player] = true;
    game.points[player] -= 1000;
    game.riichiSticks += 1;
    pool = hand.filter((tile) => riichiCandidates.includes(tile.id));
    setStatus(`${player === 1 ? "CPU 青" : "CPU 橙"}がリーチ！`);
    render();
  }
  if (game.riichi[player] && game.drawnId) pool = hand.filter((tile) => tile.id === game.drawnId);
  for (const tile of pool) {
    const remaining = hand.filter((item) => item.id !== tile.id);
    const score = handPotential(remaining);
    if (score > bestScore) {
      bestScore = score;
      candidates = [tile];
    } else if (score === bestScore) candidates.push(tile);
  }
  const choice = candidates[Math.floor(Math.random() * candidates.length)];
  setStatus(`${player === 1 ? "CPU 青" : "CPU 橙"}が ${displayName(choice.code)} を打牌`);
  discard(player, choice.id);
}

function cpuNukiThenDiscard(player) {
  const north = game.hands[player].find((tile) => canonical(tile.code) === "4z");
  if (!north) { cpuDiscard(player); return; }
  game.hands[player] = game.hands[player].filter((tile) => tile.id !== north.id);
  game.nuki[player] += 1;
  setStatus(`${player === 1 ? "CPU 青" : "CPU 橙"}が北を抜きました`);
  if (!drawTile(player) || game.over) return;
  render();
  if (isWinning(game.hands[player])) {
    timer = setTimeout(() => finishGame("ツモ", `${player === 1 ? "CPU 青" : "CPU 橙"}のツモ和了です`, game.hands[player], "和"), 400);
    return;
  }
  timer = setTimeout(() => cpuNukiThenDiscard(player), 300);
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

function isWinning(hand) {
  if (hand.length % 3 !== 2) return false;
  const counts = toCounts(hand);
  if (isKokushi(counts) || counts.filter((n) => n === 2).length === 7) return true;
  for (let pair = 0; pair < counts.length; pair += 1) {
    if (counts[pair] < 2) continue;
    counts[pair] -= 2;
    if (canFormMelds(counts)) {
      counts[pair] += 2;
      return true;
    }
    counts[pair] += 2;
  }
  return false;
}

function isTenpai(hand) {
  if (hand.length % 3 !== 1) return false;
  const counts = toCounts(hand);
  return TILE_TYPES.some((code, index) => counts[index] < 4 && isWinning([...hand, { id: -1, code }]));
}

function getRiichiDiscards(hand) {
  if (hand.length % 3 !== 2) return [];
  return hand.filter((tile) => isTenpai(hand.filter((item) => item.id !== tile.id))).map((tile) => tile.id);
}

function canFormMelds(counts) {
  const first = counts.findIndex((count) => count > 0);
  if (first === -1) return true;
  if (counts[first] >= 3) {
    counts[first] -= 3;
    if (canFormMelds(counts)) { counts[first] += 3; return true; }
    counts[first] += 3;
  }
  const type = TILE_TYPES[first];
  const number = Number(type[0]);
  const suit = type[1];
  if ((suit === "p" || suit === "s") && number <= 7) {
    const second = tileIndex(`${number + 1}${suit}`);
    const third = tileIndex(`${number + 2}${suit}`);
    if (counts[second] && counts[third]) {
      counts[first] -= 1; counts[second] -= 1; counts[third] -= 1;
      if (canFormMelds(counts)) { counts[first] += 1; counts[second] += 1; counts[third] += 1; return true; }
      counts[first] += 1; counts[second] += 1; counts[third] += 1;
    }
  }
  return false;
}

function isKokushi(counts) {
  const required = ["1m", "9m", "1p", "9p", "1s", "9s", ...range(1, 7, "z")].map(tileIndex);
  return required.every((i) => counts[i] >= 1) && required.some((i) => counts[i] >= 2);
}

function refreshActions() {
  hideActions();
  if (game.over || game.turn !== 0 || game.busy) return;
  if (isWinning(game.hands[0])) {
    els.winAction.textContent = "ツモ";
    els.winAction.hidden = false;
  }
  if (!game.riichi[0] && getRiichiDiscards(game.hands[0]).length) els.reachAction.hidden = false;
  if (game.hands[0].some((tile) => canonical(tile.code) === "4z")) els.northAction.hidden = false;
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
  if (game.waitingRon) {
    finishGame("ロン！", "あなたの和了です", [...game.hands[0], game.pendingRonTile], "和");
  } else if (isWinning(game.hands[0])) {
    finishGame("ツモ！", "あなたの和了です", game.hands[0], "和");
  }
}

function passRon() {
  const discarder = game.turn;
  game.waitingRon = false;
  game.pendingRonTile = null;
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
  if (game.turn !== 0 || game.busy || game.over) return;
  const north = game.hands[0].find((tile) => canonical(tile.code) === "4z");
  if (!north) return;
  game.busy = true;
  game.hands[0] = game.hands[0].filter((tile) => tile.id !== north.id);
  game.nuki[0] += 1;
  setStatus("北を抜きました。嶺上牌をツモします");
  vibrate(25);
  timer = setTimeout(() => {
    if (!drawTile(0) || game.over) return;
    game.busy = false;
    render();
    refreshActions();
  }, 280);
}

function finishGame(title, detail, hand, mark) {
  game.over = true;
  game.busy = true;
  clearTimeout(timer);
  hideActions();
  els.resultTitle.textContent = title;
  els.resultDetail.textContent = detail;
  els.resultMark.textContent = mark;
  els.resultHand.innerHTML = hand ? [...hand].sort(tileSort).map((tile) => tileImage(tile, "")).join("") : "";
  renderHand();
  vibrate([40, 30, 80]);
  setTimeout(() => els.dialog.showModal(), 180);
}

function render() {
  renderHand();
  game.rivers.forEach((river, player) => {
    els.rivers[player].innerHTML = river.map((tile) => tileImage(tile, displayName(tile.code))).join("");
  });
  els.wallCount.textContent = `残り ${game.wall.length}`;
  els.cpuCounts.forEach((el, index) => { el.textContent = game.hands[index + 1].length; });
  els.scores.forEach((el, index) => { el.textContent = game.points[index].toLocaleString("ja-JP"); });
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
    const drawn = tile.id === game.drawnId ? " drawn" : "";
    const candidate = candidates.includes(tile.id) ? " riichi-candidate" : "";
    const riichiLocked = game.riichi[0] && tile.id !== game.drawnId;
    const invalidDeclaration = game.declaringRiichi && !candidates.includes(tile.id);
    return `<button class="tile-button${drawn}${candidate}" type="button" data-id="${tile.id}" aria-label="${displayName(tile.code)}を打つ" ${game.turn !== 0 || game.busy || game.over || riichiLocked || invalidDeclaration ? "disabled" : ""}>${tileImage(tile, "")}</button>`;
  }).join("");
}

function tileImage(tile, alt) {
  return `<img src="${tileSrc(tile.code)}" alt="${alt}" draggable="false" />`;
}

function tileSrc(code) {
  return `./img/hai/${code}.png`;
}

function displayName(code) {
  const numberNames = ["赤五", "一", "二", "三", "四", "五", "六", "七", "八", "九"];
  if (code[1] === "z") return ["", "東", "南", "西", "北", "白", "發", "中"][Number(code[0])];
  return `${numberNames[Number(code[0])]}${{ m: "萬", p: "筒", s: "索" }[code[1]]}`;
}

function setStatus(message) {
  els.status.textContent = message;
}

function vibrate(pattern) {
  if (navigator.vibrate) navigator.vibrate(pattern);
}

// Small, read-only hook used by the repository's rule smoke tests.
Object.defineProperty(globalThis, "__sanmaEngine", {
  value: Object.freeze({ isWinning, isTenpai, getRiichiDiscards, canonical, tileIndex }),
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
document.querySelector("#new-game").addEventListener("click", startGame);
document.querySelector("#play-again").addEventListener("click", startGame);

startGame();
