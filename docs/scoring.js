(function attachSanmaScoring(global) {
  "use strict";

  const TYPES = ["1m", "9m", ...range(1, 9, "p"), ...range(1, 9, "s"), ...range(1, 7, "z")];
  const KOKUSHI = ["1m", "9m", "1p", "9p", "1s", "9s", ...range(1, 7, "z")];
  const GREEN = new Set(["2s", "3s", "4s", "6s", "8s", "6z"]);

  function range(from, to, suit) {
    return Array.from({ length: to - from + 1 }, (_, i) => `${from + i}${suit}`);
  }

  function canonical(code) {
    return code && code[0] === "0" ? `5${code[1]}` : code;
  }

  function tileIndex(code) {
    return TYPES.indexOf(canonical(code));
  }

  function toCounts(codes) {
    const counts = Array(TYPES.length).fill(0);
    for (const raw of codes) {
      const index = tileIndex(raw);
      if (index < 0) throw new Error(`Unknown tile: ${raw}`);
      counts[index] += 1;
    }
    return counts;
  }

  function isHonorIndex(index) {
    return TYPES[index][1] === "z";
  }

  function isTerminalIndex(index) {
    const code = TYPES[index];
    return code[1] !== "z" && (code[0] === "1" || code[0] === "9");
  }

  function isYaochuIndex(index) {
    return isHonorIndex(index) || isTerminalIndex(index);
  }

  function isKokushi(counts) {
    const required = KOKUSHI.map(tileIndex);
    return required.every((index) => counts[index] >= 1) && required.some((index) => counts[index] >= 2);
  }

  function isChiitoitsu(counts) {
    return counts.filter((count) => count === 2).length === 7;
  }

  function findDecompositions(counts, groupsNeeded = 4) {
    const results = [];
    for (let pair = 0; pair < counts.length; pair += 1) {
      if (counts[pair] < 2) continue;
      counts[pair] -= 2;
      collectMelds(counts, [], groupsNeeded, (groups) => results.push({ pair, groups }));
      counts[pair] += 2;
    }
    return results;
  }

  function collectMelds(counts, groups, groupsNeeded, done) {
    const first = counts.findIndex((count) => count > 0);
    if (first === -1) {
      if (groups.length === groupsNeeded) done(groups.map((group) => ({ ...group })));
      return;
    }
    if (groups.length >= groupsNeeded) return;

    if (counts[first] >= 3) {
      counts[first] -= 3;
      groups.push({ type: "triplet", tile: first });
      collectMelds(counts, groups, groupsNeeded, done);
      groups.pop();
      counts[first] += 3;
    }

    const code = TYPES[first];
    const number = Number(code[0]);
    const suit = code[1];
    if ((suit === "p" || suit === "s") && number <= 7) {
      const second = tileIndex(`${number + 1}${suit}`);
      const third = tileIndex(`${number + 2}${suit}`);
      if (counts[second] > 0 && counts[third] > 0) {
        counts[first] -= 1;
        counts[second] -= 1;
        counts[third] -= 1;
        groups.push({ type: "sequence", tile: first });
        collectMelds(counts, groups, groupsNeeded, done);
        groups.pop();
        counts[first] += 1;
        counts[second] += 1;
        counts[third] += 1;
      }
    }
  }

  function isWinning(codes, melds = []) {
    if (codes.length + melds.length * 3 !== 14) return false;
    const counts = toCounts(codes);
    if (!melds.length && (isKokushi(counts) || isChiitoitsu(counts))) return true;
    return findDecompositions(counts, 4 - melds.length).length > 0;
  }

  function isTenpai(codes, melds = []) {
    if (codes.length + melds.length * 3 !== 13) return false;
    const counts = toCounts(codes);
    return TYPES.some((code, index) => counts[index] < 4 && isWinning([...codes, code], melds));
  }

  function getPlacements(decomposition, winIndex) {
    const placements = [];
    if (decomposition.pair === winIndex) placements.push({ kind: "pair", groupIndex: -1, wait: "tanki" });
    decomposition.groups.forEach((group, groupIndex) => {
      if (group.type === "triplet" && group.tile === winIndex) {
        placements.push({ kind: "triplet", groupIndex, wait: "shanpon" });
      }
      if (group.type === "sequence" && winIndex >= group.tile && winIndex <= group.tile + 2) {
        const start = Number(TYPES[group.tile][0]);
        const winNumber = Number(TYPES[winIndex][0]);
        let wait = "ryanmen";
        if (winNumber === start + 1) wait = "kanchan";
        else if ((start === 1 && winNumber === 3) || (start === 7 && winNumber === 7)) wait = "penchan";
        placements.push({ kind: "sequence", groupIndex, wait });
      }
    });
    return placements.length ? placements : [{ kind: "unknown", groupIndex: -1, wait: "ryanmen" }];
  }

  function addCommonYaku(yaku, codes, context) {
    if (context.doubleRiichi) yaku.push({ name: "ダブルリーチ", han: 2 });
    else if (context.riichi) yaku.push({ name: "リーチ", han: 1 });
    if (context.riichi && context.ippatsu) yaku.push({ name: "一発", han: 1 });
    if (context.winType === "tsumo" && context.closed) yaku.push({ name: "門前清自摸和", han: 1 });
    if (context.haitei) yaku.push({ name: context.winType === "tsumo" ? "海底摸月" : "河底撈魚", han: 1 });
    if (context.rinshan && context.winType === "tsumo") yaku.push({ name: "嶺上開花", han: 1 });
    if (context.chankan && context.winType === "ron") yaku.push({ name: "槍槓", han: 1 });
    if (codes.every((code) => {
      const index = tileIndex(code);
      return !isYaochuIndex(index);
    })) yaku.push({ name: "断么九", han: 1 });
  }

  function addFlushYaku(yaku, codes, closed = true) {
    const canonicalCodes = codes.map(canonical);
    const suits = new Set(canonicalCodes.filter((code) => code[1] !== "z").map((code) => code[1]));
    const hasHonors = canonicalCodes.some((code) => code[1] === "z");
    if (suits.size === 1 && !hasHonors) yaku.push({ name: "清一色", han: closed ? 6 : 5 });
    else if (suits.size === 1 && hasHonors) yaku.push({ name: "混一色", han: closed ? 3 : 2 });
  }

  function yakumanForTiles(codes) {
    const canonicalCodes = codes.map(canonical);
    const yakuman = [];
    if (canonicalCodes.every((code) => code[1] === "z")) yakuman.push("字一色");
    if (canonicalCodes.every((code) => isTerminalIndex(tileIndex(code)))) yakuman.push("清老頭");
    if (canonicalCodes.every((code) => GREEN.has(code))) yakuman.push("緑一色");
    return yakuman;
  }

  function evaluateChiitoitsu(codes, context) {
    const yakuman = yakumanForTiles(codes);
    if (yakuman.length) return finalize([], 25, yakuman, codes, context);

    const yaku = [{ name: "七対子", han: 2 }];
    addCommonYaku(yaku, codes, context);
    addFlushYaku(yaku, codes, true);
    if (codes.map(tileIndex).every(isYaochuIndex)) yaku.push({ name: "混老頭", han: 2 });
    return finalize(yaku, 25, [], codes, context);
  }

  function evaluateNormal(codes, decomposition, placement, context) {
    const yaku = [];
    const patternCodes = context.allCodes || codes;
    const yakuman = yakumanForTiles(patternCodes);
    const fixedGroups = (context.melds || []).map((meld) => ({
      type: "triplet",
      tile: tileIndex(meld.code),
      open: meld.type !== "ankan",
      kan: meld.type !== "pon",
    }));
    const concealedGroups = decomposition.groups.map((group) => ({ ...group, open: false, kan: false }));
    const allGroups = [...concealedGroups, ...fixedGroups];
    const triplets = allGroups.filter((group) => group.type === "triplet");
    const sequences = allGroups.filter((group) => group.type === "sequence");
    const tripletTiles = new Set(triplets.map((group) => group.tile));
    const pairCode = TYPES[decomposition.pair];

    const dragonTriplets = [5, 6, 7].filter((n) => tripletTiles.has(tileIndex(`${n}z`)));
    const windTriplets = [1, 2, 3, 4].filter((n) => tripletTiles.has(tileIndex(`${n}z`)));
    const windPair = pairCode[1] === "z" && Number(pairCode[0]) <= 4;
    if (dragonTriplets.length === 3) yakuman.push("大三元");
    if (windTriplets.length === 4) yakuman.push("大四喜");
    else if (windTriplets.length === 3 && windPair) yakuman.push("小四喜");

    let concealedTriplets = triplets.filter((group) => !group.open).length;
    if (context.winType === "ron" && placement.kind === "triplet") concealedTriplets -= 1;
    if (concealedTriplets === 4) yakuman.push("四暗刻");
    if (yakuman.length) return finalize([], 0, [...new Set(yakuman)], codes, context);

    addCommonYaku(yaku, patternCodes, context);
    addFlushYaku(yaku, patternCodes, context.closed);

    for (const group of triplets) {
      const code = TYPES[group.tile];
      if (code[1] !== "z") continue;
      const n = Number(code[0]);
      if (n >= 5) yaku.push({ name: `役牌 ${["", "", "", "", "", "白", "發", "中"][n]}`, han: 1 });
      if (n === context.seatWind) yaku.push({ name: "自風牌", han: 1 });
      if (n === context.roundWind) yaku.push({ name: "場風牌", han: 1 });
    }

    if (context.closed && sequences.length === 4 && pairFu(decomposition.pair, context) === 0 && placement.wait === "ryanmen") {
      yaku.push({ name: "平和", han: 1 });
    }

    const sequenceCounts = new Map();
    for (const group of sequences) sequenceCounts.set(group.tile, (sequenceCounts.get(group.tile) || 0) + 1);
    const sequencePairs = [...sequenceCounts.values()].reduce((sum, count) => sum + Math.floor(count / 2), 0);
    if (context.closed && sequencePairs >= 2) yaku.push({ name: "二盃口", han: 3 });
    else if (context.closed && sequencePairs === 1) yaku.push({ name: "一盃口", han: 1 });

    for (const suit of ["p", "s"]) {
      const starts = [1, 4, 7].map((n) => tileIndex(`${n}${suit}`));
      if (starts.every((index) => sequenceCounts.has(index))) yaku.push({ name: "一気通貫", han: context.closed ? 2 : 1 });
    }

    if (triplets.length === 4) yaku.push({ name: "対々和", han: 2 });
    if (concealedTriplets >= 3) yaku.push({ name: "三暗刻", han: 2 });
    if (triplets.filter((group) => group.kan).length >= 3) yaku.push({ name: "三槓子", han: 2 });
    for (const n of [1, 9]) {
      if (["m", "p", "s"].every((suit) => tripletTiles.has(tileIndex(`${n}${suit}`)))) {
        yaku.push({ name: "三色同刻", han: 2 });
      }
    }

    const allGroupsYaochu = decomposition.groups.every((group) => group.type === "sequence"
      ? [1, 7].includes(Number(TYPES[group.tile][0]))
      : isYaochuIndex(group.tile));
    if (allGroupsYaochu && isYaochuIndex(decomposition.pair) && sequences.length > 0) {
      const hasHonor = patternCodes.some((code) => canonical(code)[1] === "z");
      const closedHan = hasHonor ? 2 : 3;
      yaku.push({ name: hasHonor ? "混全帯么九" : "純全帯么九", han: context.closed ? closedHan : closedHan - 1 });
    }

    if (patternCodes.map(tileIndex).every(isYaochuIndex)) yaku.push({ name: "混老頭", han: 2 });
    if (dragonTriplets.length === 2 && [5, 6, 7].includes(Number(pairCode[0])) && pairCode[1] === "z") {
      yaku.push({ name: "小三元", han: 2 });
    }

    let fu = 20;
    const pinfu = yaku.some((item) => item.name === "平和");
    if (context.winType === "ron" && context.closed) fu += 10;
    else if (!pinfu) fu += 2;
    fu += pairFu(decomposition.pair, context);
    if (["tanki", "kanchan", "penchan"].includes(placement.wait)) fu += 2;
    allGroups.forEach((group, groupIndex) => {
      if (group.type !== "triplet") return;
      const openByRon = !group.open && context.winType === "ron" && placement.groupIndex === groupIndex && placement.kind === "triplet";
      const terminalOrHonor = isYaochuIndex(group.tile);
      const open = group.open || openByRon;
      let groupFu = open ? (terminalOrHonor ? 4 : 2) : (terminalOrHonor ? 8 : 4);
      if (group.kan) groupFu *= 4;
      fu += groupFu;
    });
    if (pinfu && context.winType === "tsumo") fu = 20;
    else fu = Math.ceil(fu / 10) * 10;
    return finalize(yaku, fu, [], codes, context);
  }

  function pairFu(pairIndex, context) {
    const code = TYPES[pairIndex];
    if (code[1] !== "z") return 0;
    const n = Number(code[0]);
    let fu = n >= 5 ? 2 : 0;
    if (n === context.seatWind) fu += 2;
    if (n === context.roundWind) fu += 2;
    return fu;
  }

  function doraFromIndicator(raw) {
    const code = canonical(raw);
    const n = Number(code[0]);
    const suit = code[1];
    if (suit === "m") return n === 1 ? "9m" : "1m";
    if (suit === "p" || suit === "s") return `${n === 9 ? 1 : n + 1}${suit}`;
    if (n <= 4) return `${n === 4 ? 1 : n + 1}z`;
    return `${n === 7 ? 5 : n + 1}z`;
  }

  function finalize(yaku, fu, yakuman, rawCodes, context) {
    if (yakuman.length) {
      return {
        yaku: yakuman.map((name) => ({ name, han: 13 })),
        han: yakuman.length * 13,
        fu,
        yakuman: yakuman.length,
        limitName: yakuman.length > 1 ? `${yakuman.length}倍役満` : "役満",
        basePoints: 8000 * yakuman.length,
      };
    }

    const yakuHan = yaku.reduce((sum, item) => sum + item.han, 0);
    if (yakuHan === 0) return null;

    const canonicalCodes = rawCodes.map(canonical);
    const indicators = context.doraIndicators || (context.doraIndicator ? [context.doraIndicator] : []);
    const doraTiles = indicators.map(doraFromIndicator);
    const dora = canonicalCodes.reduce((sum, code) => sum + doraTiles.filter((tile) => tile === code).length, 0)
      + (context.melds || []).reduce((sum, meld) => {
        const copies = meld.type === "pon" ? 3 : 4;
        return sum + doraTiles.filter((tile) => tile === canonical(meld.code)).length * copies;
      }, 0);
    const red = rawCodes.filter((code) => code && code[0] === "0").length
      + (context.melds || []).reduce((sum, meld) => sum + (meld.tiles || []).filter((code) => code && code[0] === "0").length, 0);
    const nuki = context.nuki || 0;
    const fullYaku = [...yaku];
    if (dora) fullYaku.push({ name: "ドラ", han: dora });
    if (red) fullYaku.push({ name: "赤ドラ", han: red });
    if (nuki) fullYaku.push({ name: "抜きドラ", han: nuki });
    const han = yakuHan + dora + red + nuki;

    let basePoints;
    let limitName = "";
    if (han >= 13) { basePoints = 8000; limitName = "数え役満"; }
    else if (han >= 11) { basePoints = 6000; limitName = "三倍満"; }
    else if (han >= 8) { basePoints = 4000; limitName = "倍満"; }
    else if (han >= 6) { basePoints = 3000; limitName = "跳満"; }
    else {
      const rawBase = fu * (2 ** (han + 2));
      if (han >= 5 || rawBase >= 2000) { basePoints = 2000; limitName = "満貫"; }
      else basePoints = rawBase;
    }
    return { yaku: fullYaku, han, fu, yakuman: 0, limitName, basePoints };
  }

  function compareScores(a, b) {
    if (!a) return b;
    if (!b) return a;
    if (a.basePoints !== b.basePoints) return a.basePoints > b.basePoints ? a : b;
    if (a.han !== b.han) return a.han > b.han ? a : b;
    return a.fu >= b.fu ? a : b;
  }

  function scoreHand(rawCodes, suppliedContext = {}) {
    const context = {
      winType: "ron",
      riichi: false,
      doubleRiichi: false,
      ippatsu: false,
      seatWind: 1,
      roundWind: 1,
      doraIndicator: null,
      nuki: 0,
      haitei: false,
      lastTile: rawCodes[rawCodes.length - 1],
      melds: [],
      ...suppliedContext,
    };
    context.closed = context.melds.every((meld) => meld.type === "ankan");
    context.allCodes = [
      ...rawCodes,
      ...context.melds.flatMap((meld) => Array(3).fill(meld.code)),
    ];
    if (rawCodes.length + context.melds.length * 3 !== 14) return null;
    const counts = toCounts(rawCodes);
    const groupsNeeded = 4 - context.melds.length;
    if (!context.melds.length && !isKokushi(counts) && !isChiitoitsu(counts) && findDecompositions([...counts], groupsNeeded).length === 0) return null;
    if (context.melds.length && findDecompositions([...counts], groupsNeeded).length === 0) return null;
    if (context.firstDraw && context.winType === "tsumo") {
      return finalize([], 0, [context.isDealer ? "天和" : "地和"], rawCodes, context);
    }
    if (!context.melds.length && isKokushi(counts)) return finalize([], 0, ["国士無双"], rawCodes, context);
    if (!context.melds.length && isChiitoitsu(counts)) return evaluateChiitoitsu(rawCodes, context);

    const decompositions = findDecompositions(counts, groupsNeeded);
    if (!decompositions.length) return null;
    const winIndex = tileIndex(context.lastTile);
    let best = null;
    for (const decomposition of decompositions) {
      for (const placement of getPlacements(decomposition, winIndex)) {
        best = compareScores(best, evaluateNormal(rawCodes, decomposition, placement, context));
      }
    }
    return best;
  }

  function roundUp100(value) {
    return Math.ceil(value / 100) * 100;
  }

  function calculatePayments(score, { winType, winner, loser = null, dealer, honba = 0 }) {
    const transfers = [0, 0, 0];
    if (winType === "ron") {
      const amount = roundUp100(score.basePoints * (winner === dealer ? 6 : 4)) + honba * 300;
      transfers[winner] += amount;
      transfers[loser] -= amount;
      return { transfers, label: `${amount.toLocaleString("ja-JP")}点` };
    }

    const payments = [];
    for (let player = 0; player < 3; player += 1) {
      if (player === winner) continue;
      const multiplier = winner === dealer ? 2 : (player === dealer ? 2 : 1);
      const amount = roundUp100(score.basePoints * multiplier) + honba * 100;
      transfers[player] -= amount;
      transfers[winner] += amount;
      payments.push({ player, amount });
    }
    return { transfers, payments, label: payments.map((item) => item.amount.toLocaleString("ja-JP")).join(" / ") };
  }

  global.SanmaScoring = Object.freeze({
    TYPES,
    canonical,
    tileIndex,
    isWinning,
    isTenpai,
    scoreHand,
    doraFromIndicator,
    calculatePayments,
  });
})(globalThis);
