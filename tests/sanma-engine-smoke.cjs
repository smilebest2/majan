const assert = require("node:assert/strict");

const element = () => ({
  hidden: false,
  open: false,
  textContent: "",
  innerHTML: "",
  src: "",
  alt: "",
  addEventListener() {},
  classList: { add() {}, toggle() {} },
  close() { this.open = false; },
  showModal() { this.open = true; },
});

const elements = new Map();
global.document = {
  querySelector(selector) {
    if (!elements.has(selector)) elements.set(selector, element());
    return elements.get(selector);
  },
};
global.navigator = { vibrate() {} };
global.localStorage = { getItem() { return null; }, setItem() {}, removeItem() {} };

require("../docs/scoring.js");
require("../docs/game.js");

const renderedHand = elements.get("#hand").innerHTML;
assert.equal((renderedHand.match(/class="tile-button/g) || []).length, 14, "player starts with 14 tiles");
assert.doesNotMatch(renderedHand, /\sdisabled(?:\s|>)/, "player tiles are enabled at the start of their turn");
assert.equal(elements.get("#pass-action").textContent, "見送る", "shared pass button starts with the call-pass label");

const { isWinning, isTenpai, getRiichiDiscards, getWaitingCodes, canRiichiAnkan, advanceMatchState } = global.__sanmaEngine;
let nextId = 1;
const hand = (codes) => codes.map((code) => ({ id: nextId++, code }));

assert.equal(isWinning(hand(["1m", "1m", "1m", "1p", "2p", "3p", "4p", "5p", "6p", "7s", "8s", "9s", "1z", "1z"])), true, "standard hand");
assert.equal(isWinning(hand(["1m", "1m", "9m", "9m", "1p", "1p", "9p", "9p", "1s", "1s", "9s", "9s", "1z", "1z"])), true, "seven pairs");
assert.equal(isWinning(hand(["1m", "9m", "1p", "9p", "1s", "9s", "1z", "2z", "3z", "4z", "5z", "6z", "7z", "7z"])), true, "thirteen orphans");
assert.equal(isWinning(hand(["1m", "1m", "1m", "1p", "2p", "4p", "4p", "5p", "6p", "7s", "8s", "9s", "1z", "1z"])), false, "incomplete hand");

const readyHand = hand(["1m", "1m", "1m", "1p", "2p", "3p", "4p", "5p", "6p", "7s", "8s", "1z", "1z"]);
assert.equal(isTenpai(readyHand), true, "tenpai detection");
const riichiHand = [...readyHand, { id: nextId++, code: "9s" }];
assert.ok(getRiichiDiscards(riichiHand).length > 0, "riichi discard candidates");

const riichiKanHand = hand(["5z", "5z", "5z", "1p", "2p", "3p", "4p", "5p", "6p", "1s", "1s", "7s", "8s"]);
const drawnDragon = { id: nextId++, code: "5z" };
riichiKanHand.push(drawnDragon);
assert.deepEqual(getWaitingCodes(riichiKanHand.filter((tile) => tile.id !== drawnDragon.id)), ["6s", "9s"], "waits before riichi ankan");
assert.equal(canRiichiAnkan(riichiKanHand, [], drawnDragon.id, "5z"), true, "riichi ankan keeps the same waits");
assert.equal(canRiichiAnkan(riichiKanHand, [], riichiKanHand.find((tile) => tile.code === "1p").id, "5z"), false, "riichi ankan must use the drawn fourth tile");

const initialMatch = { points: [35000, 35000, 35000], dealer: 0, roundWind: 0, handNumber: 0, honba: 0, riichiSticks: 1 };
assert.deepEqual(advanceMatchState(initialMatch, true, false), { ...initialMatch, honba: 1 }, "dealer repeat");
assert.deepEqual(advanceMatchState(initialMatch, false, false), { ...initialMatch, dealer: 1, handNumber: 1, honba: 0 }, "dealer rotation");
const southThree = { ...initialMatch, dealer: 2, roundWind: 1, handNumber: 2 };
assert.deepEqual(advanceMatchState(southThree, false, false), { ...southThree, dealer: 0, roundWind: 2, handNumber: 0 }, "west extension transition");

console.log("sanma engine smoke tests passed");
