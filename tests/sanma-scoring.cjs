const assert = require("node:assert/strict");

require("../docs/scoring.js");
const { isWinning, isTenpai, scoreHand, calculatePayments, doraFromIndicator } = global.SanmaScoring;

const pinfu = ["1p", "2p", "3p", "4p", "5p", "6p", "7p", "8p", "9p", "2s", "3s", "4s", "5s", "5s"];
const pinfuScore = scoreHand(pinfu, { winType: "tsumo", riichi: true, lastTile: "4s", seatWind: 1, roundWind: 1 });
assert.ok(pinfuScore, "scorable closed hand");
assert.ok(pinfuScore.yaku.some((item) => item.name === "平和"), "pinfu yaku");
assert.ok(pinfuScore.yaku.some((item) => item.name === "一気通貫"), "ikkitsuukan yaku");
const doubleReachScore = scoreHand(pinfu, { winType: "tsumo", riichi: true, doubleRiichi: true, ippatsu: true, lastTile: "4s" });
assert.ok(doubleReachScore.yaku.some((item) => item.name === "ダブルリーチ"), "double riichi");
assert.ok(doubleReachScore.yaku.some((item) => item.name === "一発"), "ippatsu");

const noYaku = ["1p", "2p", "3p", "4p", "5p", "6p", "7s", "8s", "9s", "2s", "3s", "4s", "1z", "1z"];
assert.equal(isWinning(noYaku), true, "complete no-yaku shape");
assert.equal(scoreHand(noYaku, { winType: "ron", lastTile: "4s", seatWind: 1, roundWind: 1 }), null, "no-yaku hand rejected");

const yakuhai = ["5z", "5z", "5z", "1p", "2p", "3p", "4p", "5p", "6p", "7s", "8s", "9s", "2z", "2z"];
const yakuhaiScore = scoreHand(yakuhai, { winType: "ron", lastTile: "5z", seatWind: 1, roundWind: 1 });
assert.ok(yakuhaiScore.yaku.some((item) => item.name.includes("白")), "dragon yaku");

const kokushi = ["1m", "9m", "1p", "9p", "1s", "9s", "1z", "2z", "3z", "4z", "5z", "6z", "7z", "7z"];
const kokushiScore = scoreHand(kokushi, { winType: "ron", lastTile: "7z" });
assert.equal(kokushiScore.limitName, "役満", "kokushi yakuman");

const ready = ["1m", "1m", "1m", "1p", "2p", "3p", "4p", "5p", "6p", "7s", "8s", "1z", "1z"];
assert.equal(isTenpai(ready), true, "tenpai");
assert.equal(doraFromIndicator("9p"), "1p", "number dora wraps");
assert.equal(doraFromIndicator("4z"), "1z", "wind dora wraps");

const ronPayment = calculatePayments({ basePoints: 2000 }, { winType: "ron", winner: 0, loser: 1, dealer: 0, honba: 1 });
assert.deepEqual(ronPayment.transfers, [12300, -12300, 0], "dealer mangan ron plus honba");
const tsumoPayment = calculatePayments({ basePoints: 2000 }, { winType: "tsumo", winner: 1, dealer: 0, honba: 0 });
assert.deepEqual(tsumoPayment.transfers, [-4000, 6000, -2000], "non-dealer sanma tsumo-loss payment");

console.log("sanma scoring tests passed");
