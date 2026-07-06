const menuButton = document.querySelector("[data-menu-button]");
const mobileNav = document.querySelector("[data-mobile-nav]");
const header = document.querySelector("[data-header]");

menuButton?.addEventListener("click", () => {
  const isOpen = menuButton.getAttribute("aria-expanded") === "true";
  menuButton.setAttribute("aria-expanded", String(!isOpen));
  mobileNav.classList.toggle("is-open", !isOpen);
  document.body.classList.toggle("menu-open", !isOpen);
});

mobileNav?.querySelectorAll("a, button").forEach((item) => {
  item.addEventListener("click", () => {
    menuButton?.setAttribute("aria-expanded", "false");
    mobileNav.classList.remove("is-open");
    document.body.classList.remove("menu-open");
  });
});

window.addEventListener("scroll", () => {
  header?.classList.toggle("is-scrolled", window.scrollY > 20);
}, { passive: true });

const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add("is-visible");
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.12, rootMargin: "0px 0px -40px" });

document.querySelectorAll(".reveal").forEach((element) => revealObserver.observe(element));
document.documentElement.classList.add("animations-ready");

const modal = document.querySelector("[data-login-modal]");
document.querySelectorAll("[data-open-login]").forEach((button) => {
  button.addEventListener("click", () => modal?.showModal());
});

if (modal && new URLSearchParams(window.location.search).get("dealer-login") === "1") {
  modal.showModal();
  window.history.replaceState(null, "", `${window.location.pathname}${window.location.hash}`);
}
document.querySelector("[data-close-login]")?.addEventListener("click", () => modal?.close());
modal?.addEventListener("click", (event) => {
  if (event.target === modal) modal.close();
});

document.querySelector("[data-login-form]")?.addEventListener("submit", (event) => {
  event.preventDefault();
  document.querySelector("[data-login-status]").textContent = "デモ画面のため、ログイン機能は現在準備中です。";
});

document.querySelectorAll("[data-contact-type]").forEach((link) => {
  link.addEventListener("click", () => {
    const select = document.querySelector('select[name="type"]');
    if (select) select.value = link.dataset.contactType;
  });
});

const contactForm = document.querySelector("[data-contact-form]");
const turnstileContainer = document.querySelector("[data-turnstile-widget]");
let turnstileWidgetId = null;

if (contactForm) {
  try {
    const calculation = window.sessionStorage.getItem("damagaFaqCalculation");
    if (calculation) {
      const message = contactForm.elements.message;
      const type = contactForm.elements.type;
      if (message) message.value = `【FAQ参考計算の結果】\n${calculation}\n\n${message.value}`;
      if (type && !type.value) type.value = "施設への導入相談";
      window.sessionStorage.removeItem("damagaFaqCalculation");
    }
  } catch (error) {
    // Storage may be unavailable in private browsing; the form remains usable.
  }
}

const setFormStatus = (message, isError = false) => {
  const status = document.querySelector("[data-form-status]");
  if (!status) return;
  status.textContent = message;
  status.classList.toggle("is-error", isError);
};

const renderTurnstile = async () => {
  if (!contactForm || !turnstileContainer) return;

  try {
    const response = await fetch("api/turnstile-config.php", {
      headers: { "Accept": "application/json" },
      cache: "no-store"
    });
    const config = response.ok ? await response.json() : {};
    const siteKey = config.siteKey;

    if (!siteKey) {
      turnstileContainer.innerHTML = "<p>セキュリティ確認の設定が未完了です。</p>";
      return;
    }

    const waitForTurnstile = () => new Promise((resolve, reject) => {
      let attempts = 0;
      const timer = window.setInterval(() => {
        attempts += 1;
        if (window.turnstile) {
          window.clearInterval(timer);
          resolve();
        } else if (attempts > 40) {
          window.clearInterval(timer);
          reject(new Error("Turnstile script was not loaded."));
        }
      }, 100);
    });

    await waitForTurnstile();
    turnstileContainer.innerHTML = "";
    turnstileWidgetId = window.turnstile.render(turnstileContainer, { sitekey: siteKey });
  } catch (error) {
    turnstileContainer.innerHTML = "<p>セキュリティ確認を読み込めませんでした。時間をおいて再度お試しください。</p>";
  }
};

renderTurnstile();

contactForm?.addEventListener("submit", async (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const submitButton = form.querySelector("[type='submit']");

  setFormStatus("送信中です。しばらくお待ちください。");
  submitButton.disabled = true;

  try {
    const response = await fetch(form.action, {
      method: "POST",
      body: new FormData(form),
      headers: { "Accept": "application/json" }
    });
    const result = await response.json().catch(() => ({}));

    if (!response.ok || !result.success) {
      throw new Error(result.message || "セキュリティ確認に失敗しました。時間をおいて再度お試しください。");
    }

    setFormStatus("ありがとうございます。お問い合わせを受け付けました。担当者よりご連絡いたします。");
    form.reset();
    if (window.turnstile && turnstileWidgetId !== null) window.turnstile.reset(turnstileWidgetId);
  } catch (error) {
    setFormStatus(error.message || "セキュリティ確認に失敗しました。時間をおいて再度お試しください。", true);
    if (window.turnstile && turnstileWidgetId !== null) window.turnstile.reset(turnstileWidgetId);
  } finally {
    submitButton.disabled = false;
  }
});

document.querySelector("[data-roi-form]")?.addEventListener("submit", (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const annualSaving = Number(form.elements.saving.value);
  const investment = Number(form.elements.investment.value);
  const result = document.querySelector("[data-roi-result]");

  if (!investment || investment <= 0) {
    result.innerHTML = "<small>想定投資回収期間</small><strong>導入費を入力してください</strong><p>1万円以上の金額で試算できます。</p>";
    return;
  }

  const paybackYears = investment / annualSaving;
  const fiveYearReturn = annualSaving * 5 - investment;
  const paybackText = paybackYears < 1
    ? `約${Math.max(1, Math.round(paybackYears * 12))}か月`
    : `約${paybackYears.toFixed(1)}年`;
  const returnLabel = fiveYearReturn >= 0 ? "5年間の削減効果 - 導入費" : "5年間で未回収となる金額";
  const returnValue = Math.abs(Math.round(fiveYearReturn)).toLocaleString("ja-JP");

  result.innerHTML = `<small>想定投資回収期間</small><strong><b>${paybackText}</b> が目安</strong><p>${returnLabel}：約${returnValue}万円</p>`;
});
