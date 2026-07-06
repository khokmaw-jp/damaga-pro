<?php $calculatorType = (string) ($item['calculator_type'] ?? ''); ?>
<section class="faq-calculator" data-faq-calculator="<?= faq_escape($calculatorType) ?>">
  <div class="faq-calculator-heading">
    <p class="eyebrow">QUICK CALCULATION</p>
    <h2>数値を入力して参考結果を見る</h2>
    <p>入力内容はこのブラウザ内で計算されます。結果は価格・削減額・効果を保証するものではありません。</p>
  </div>
  <form class="faq-calculator-form" data-calculator-form>
    <div class="faq-calculator-fields" data-calculator-fields></div>
    <button class="button button-primary" type="submit">参考結果を計算する</button>
  </form>
  <div class="faq-calculator-result" data-calculator-result aria-live="polite">
    <small>REFERENCE RESULT</small>
    <strong>数値を入力すると、ここに結果が表示されます。</strong>
  </div>
  <a class="button button-accent faq-calculator-contact" href="/#contact" data-use-calculation hidden>この結果を使って相談する <span>→</span></a>
</section>
