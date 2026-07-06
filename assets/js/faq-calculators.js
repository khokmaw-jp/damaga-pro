const calculatorDefinitions = {
  "window-area": {
    fields: [
      { name: "width", label: "窓1枚の幅", unit: "cm", min: 1 },
      { name: "height", label: "窓1枚の高さ", unit: "cm", min: 1 },
      { name: "count", label: "同じ寸法の窓", unit: "枚", min: 1, step: 1 }
    ],
    calculate: ({ width, height, count }) => {
      const each = width * height / 10000;
      const total = each * count;
      return result(`概算窓面積 ${decimal(total)} m²`, [`1枚あたり：${decimal(each)} m²`, `対象枚数：${integer(count)}枚`], `概算窓面積：${decimal(total)} m²（${integer(count)}枚）`);
    }
  },
  "installation-area": {
    fields: [
      { name: "width", label: "窓1枚の幅", unit: "cm", min: 1 },
      { name: "height", label: "窓1枚の高さ", unit: "cm", min: 1 },
      { name: "count", label: "窓の枚数", unit: "枚", min: 1, step: 1 },
      { name: "ratio", label: "施工を予定する割合", unit: "%", min: 1, max: 100 }
    ],
    calculate: ({ width, height, count, ratio }) => {
      const all = width * height / 10000 * count;
      const target = all * ratio / 100;
      return result(`概算施工面積 ${decimal(target)} m²`, [`窓全体：${decimal(all)} m²`, `予定割合：${decimal(ratio)}%`], `概算施工面積：${decimal(target)} m²（全体${decimal(all)} m²の${decimal(ratio)}%）`);
    }
  },
  "aircon-cost": {
    fields: [
      { name: "monthly", label: "月平均の電気代", unit: "円", min: 1, step: 1000 },
      { name: "share", label: "空調が占める仮定割合", unit: "%", min: 1, max: 100 }
    ],
    calculate: ({ monthly, share }) => {
      const annual = monthly * 12;
      const aircon = annual * share / 100;
      return result(`年間空調費の参考額 ${yen(aircon)}`, [`年間電気代：${yen(annual)}`, `空調割合：${decimal(share)}%`], `年間電気代：${yen(annual)}、空調費参考額：${yen(aircon)}（仮定割合${decimal(share)}%）`);
    }
  },
  "savings": {
    fields: [
      { name: "annual", label: "年間空調費", unit: "円", min: 1, step: 10000 },
      { name: "rate", label: "提案時の仮定削減率", unit: "%", min: 0.1, max: 100, step: 0.1 }
    ],
    calculate: ({ annual, rate }) => {
      const saving = annual * rate / 100;
      return result(`年間削減額の参考値 ${yen(saving)}`, [`月平均：${yen(saving / 12)}`, `仮定削減率：${decimal(rate)}%`], `年間削減額参考値：${yen(saving)}（年間空調費${yen(annual)}、仮定削減率${decimal(rate)}%）`);
    }
  },
  "roi": {
    fields: [
      { name: "investment", label: "想定導入費", unit: "円", min: 1, step: 10000 },
      { name: "saving", label: "年間削減額の仮定値", unit: "円", min: 1, step: 10000 }
    ],
    calculate: ({ investment, saving }) => {
      const years = investment / saving;
      return result(`単純投資回収 約${decimal(years)}年`, [`月数換算：約${integer(years * 12)}か月`, "保守費・金利・料金変動は含みません"], `単純回収期間：約${decimal(years)}年（導入費${yen(investment)}、年間削減額${yen(saving)}）`);
    }
  },
  "solar-exposure": {
    fields: [
      { name: "north", label: "北向きの窓", unit: "枚", min: 0, step: 1 },
      { name: "east", label: "東向きの窓", unit: "枚", min: 0, step: 1 },
      { name: "south", label: "南向きの窓", unit: "枚", min: 0, step: 1 },
      { name: "west", label: "西向きの窓", unit: "枚", min: 0, step: 1 },
      { name: "hours", label: "日射が気になる時間", unit: "時間/日", min: 0.1, max: 24, step: 0.1 }
    ],
    calculate: ({ north, east, south, west, hours }) => {
      const total = north + east + south + west;
      if (total <= 0) throw new Error("窓の枚数を1枚以上入力してください。");
      const southWest = south + west;
      return result(`対象窓 ${integer(total)}枚`, [`南・西向き：${integer(southWest)}枚`, `窓・日射時間指標：${decimal(total * hours)} 枚・時間/日`], `対象窓：${integer(total)}枚、南・西向き：${integer(southWest)}枚、日射時間：${decimal(hours)}時間/日`);
    }
  },
  "facility-operation": {
    fields: [
      { name: "area", label: "対象区画の面積", unit: "m²", min: 1, step: 1 },
      { name: "hours", label: "1日の稼働時間", unit: "時間", min: 0.1, max: 24, step: 0.1 },
      { name: "days", label: "月間稼働日数", unit: "日", min: 1, max: 31, step: 1 }
    ],
    calculate: ({ area, hours, days }) => {
      const operatingHours = hours * days;
      const index = area * operatingHours;
      return result(`月間稼働時間 ${decimal(operatingHours)}時間`, [`対象面積：${decimal(area)} m²`, `面積・稼働時間指標：${integer(index)} m²・時間/月`], `対象面積：${decimal(area)} m²、月間稼働：${decimal(operatingHours)}時間、面積・稼働時間指標：${integer(index)} m²・時間/月`);
    }
  },
  "schedule": {
    fields: [{ name: "target", label: "施工希望日", type: "date" }],
    calculate: ({ target }) => {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const date = new Date(`${target}T00:00:00`);
      const days = Math.ceil((date - today) / 86400000);
      if (!Number.isFinite(days) || days < 0) throw new Error("今日以降の希望日を入力してください。");
      return result(`施工希望日まで ${integer(days)}日`, [`おおよそ：${decimal(days / 30.4)}か月`, `希望日：${target.replaceAll("-", "/")}`], `施工希望日：${target}（本日から${integer(days)}日、約${decimal(days / 30.4)}か月）`);
    }
  },
  "performance": {
    fields: [],
    calculate: () => result("DAMAGA PRO 公式掲載値", ["赤外線カット：約90%", "可視光透過率：約73%", "UVカット：約99.7%"], "確認した性能値：赤外線カット約90%、可視光透過率約73%、UVカット約99.7%")
  },
  "before-after": {
    fields: [
      { name: "tempBefore", label: "施工前の温度", unit: "℃", step: 0.1 },
      { name: "tempAfter", label: "施工後の温度", unit: "℃", step: 0.1 },
      { name: "powerBefore", label: "施工前の電力使用量", unit: "kWh", min: 0.1, step: 0.1 },
      { name: "powerAfter", label: "施工後の電力使用量", unit: "kWh", min: 0, step: 0.1 }
    ],
    calculate: ({ tempBefore, tempAfter, powerBefore, powerAfter }) => {
      const tempDiff = tempBefore - tempAfter;
      const powerDiff = powerBefore - powerAfter;
      const rate = powerDiff / powerBefore * 100;
      return result(`電力使用量の差 ${signed(powerDiff)} kWh`, [`変化率：${signed(rate)}%`, `温度差：${signed(tempDiff)}℃`], `施工前後比較：温度差${signed(tempDiff)}℃、電力使用量差${signed(powerDiff)} kWh、変化率${signed(rate)}%`);
    }
  }
};

const numberFormatter = new Intl.NumberFormat("ja-JP", { maximumFractionDigits: 2 });
const integerFormatter = new Intl.NumberFormat("ja-JP", { maximumFractionDigits: 0 });
const decimal = (value) => numberFormatter.format(value);
const integer = (value) => integerFormatter.format(Math.round(value));
const yen = (value) => `${integer(value)}円`;
const signed = (value) => `${value > 0 ? "-" : value < 0 ? "+" : "±"}${decimal(Math.abs(value))}`;
const result = (headline, details, summary) => ({ headline, details, summary });

document.querySelectorAll("[data-faq-calculator]").forEach((calculator) => {
  const definition = calculatorDefinitions[calculator.dataset.faqCalculator];
  if (!definition) return;

  const form = calculator.querySelector("[data-calculator-form]");
  const fieldsContainer = calculator.querySelector("[data-calculator-fields]");
  const resultContainer = calculator.querySelector("[data-calculator-result]");
  const contactLink = calculator.querySelector("[data-use-calculation]");
  const submitButton = form.querySelector("[type='submit']");

  definition.fields.forEach((field) => {
    const label = document.createElement("label");
    const labelText = document.createElement("span");
    labelText.textContent = field.label;
    const inputWrap = document.createElement("span");
    inputWrap.className = "faq-calculator-input";
    const input = document.createElement("input");
    input.name = field.name;
    input.type = field.type || "number";
    input.required = true;
    if (field.min !== undefined) input.min = String(field.min);
    if (field.max !== undefined) input.max = String(field.max);
    if (field.step !== undefined) input.step = String(field.step);
    if (input.type === "number") input.inputMode = "decimal";
    inputWrap.append(input);
    if (field.unit) {
      const unit = document.createElement("b");
      unit.textContent = field.unit;
      inputWrap.append(unit);
    }
    label.append(labelText, inputWrap);
    fieldsContainer.append(label);
  });

  const showResult = () => {
    try {
      const values = {};
      definition.fields.forEach((field) => {
        const input = form.elements[field.name];
        values[field.name] = field.type === "date" ? input.value : Number(input.value);
        if (field.type !== "date" && !Number.isFinite(values[field.name])) throw new Error("すべての数値を入力してください。");
      });
      const calculated = definition.calculate(values);
      resultContainer.innerHTML = "";
      const label = document.createElement("small");
      label.textContent = "REFERENCE RESULT";
      const headline = document.createElement("strong");
      headline.textContent = calculated.headline;
      const list = document.createElement("ul");
      calculated.details.forEach((detail) => {
        const item = document.createElement("li");
        item.textContent = detail;
        list.append(item);
      });
      resultContainer.append(label, headline, list);
      calculator.dataset.calculationSummary = calculated.summary;
      contactLink.hidden = false;
    } catch (error) {
      resultContainer.innerHTML = "";
      const message = document.createElement("strong");
      message.textContent = error.message || "入力内容を確認してください。";
      resultContainer.append(message);
      contactLink.hidden = true;
    }
  };

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    showResult();
  });

  contactLink.addEventListener("click", () => {
    const summary = calculator.dataset.calculationSummary;
    if (!summary) return;
    try {
      window.sessionStorage.setItem("damagaFaqCalculation", `${document.querySelector("h1")?.textContent || "FAQ数値計算"}\n${summary}`);
    } catch (error) {
      // Storage may be unavailable in private browsing; navigation still works.
    }
  });

  if (definition.fields.length === 0) {
    submitButton.textContent = "公式掲載値を表示する";
  }
});
