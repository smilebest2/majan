const assert = require("node:assert/strict");

const element = () => ({
  hidden: false,
  open: false,
  textContent: "",
  innerHTML: "",
  src: "",
  alt: "",
  addEventListener() {},
  close() { this.open = false; },
  showModal() { this.open = true; },
});

global.document = { querySelector: () => element() };
global.navigator = { vibrate() {} };

require("../docs/game.js");

const { isWinning, isTenpai, getRiichiDiscards } = global.__sanmaEngine;
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

console.log("sanma engine smoke tests passed");
