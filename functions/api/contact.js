const TURNSTILE_ERROR_MESSAGE = "セキュリティ確認に失敗しました。時間をおいて再度お試しください。";
const TURNSTILE_NOT_CONFIGURED_MESSAGE = "セキュリティ確認の設定が未完了です。";
const MAIL_NOT_CONFIGURED_MESSAGE = "メール送信設定が未完了です。管理者にお問い合わせください。";
const SITEVERIFY_URL = "https://challenges.cloudflare.com/turnstile/v0/siteverify";
const RESEND_EMAIL_URL = "https://api.resend.com/emails";
const PRODUCTION_HOSTNAMES = new Set(["damaga-pro.jp", "www.damaga-pro.jp"]);
const LOCAL_HOSTNAMES = new Set(["localhost", "127.0.0.1", "::1"]);

const json = (payload, status = 200, headers = {}) => Response.json(payload, {
  status,
  headers: {
    "Cache-Control": "no-store",
    ...headers,
  },
});

const cleanField = (value = "", maxLength = 1000) => String(value)
  .trim()
  .replace(/[\r\n]+/g, " ")
  .slice(0, maxLength);

const truncateText = (value = "", maxLength = 3000) => String(value).trim().slice(0, maxLength);

const getClientIp = (request) => {
  const cfIp = request.headers.get("CF-Connecting-IP");
  if (cfIp && cfIp.trim()) return cfIp.trim();

  const forwardedFor = request.headers.get("X-Forwarded-For");
  if (forwardedFor && forwardedFor.trim()) {
    return forwardedFor.split(",")[0].trim();
  }

  return "";
};

const isAllowedHostname = (hostname, request) => {
  if (!hostname) return false;
  if (PRODUCTION_HOSTNAMES.has(hostname)) return true;

  const requestHost = new URL(request.url).hostname;
  const isLocalRequest = LOCAL_HOSTNAMES.has(requestHost);
  return isLocalRequest && LOCAL_HOSTNAMES.has(hostname);
};

const verifyTurnstile = async ({ env, request, token, remoteIp }) => {
  const secret = env.TURNSTILE_SECRET_KEY || "";
  if (!secret || !token) {
    return {
      ok: false,
      notConfigured: !secret,
      result: null,
    };
  }

  const body = new URLSearchParams({
    secret,
    response: token,
  });
  if (remoteIp) body.set("remoteip", remoteIp);

  const response = await fetch(SITEVERIFY_URL, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body,
  });

  const result = await response.json();
  const hostname = typeof result.hostname === "string" ? result.hostname : "";
  const success = result.success === true;
  const errorCodes = Array.isArray(result["error-codes"]) ? result["error-codes"] : [];

  return {
    ok: success && isAllowedHostname(hostname, request),
    notConfigured: false,
    result: {
      success,
      hostname,
      errorCodes,
    },
  };
};

const sendResendEmail = async ({ env, to, from, replyTo, subject, text }) => {
  const apiKey = env.RESEND_API_KEY || "";
  if (!apiKey) return false;

  const response = await fetch(RESEND_EMAIL_URL, {
    method: "POST",
    headers: {
      "Authorization": `Bearer ${apiKey}`,
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      from,
      to,
      reply_to: replyTo,
      subject,
      text,
    }),
  });

  return response.ok;
};

const handlePost = async ({ request, env }) => {
  const formData = await request.formData();
  const fields = Object.fromEntries(formData.entries());
  const token = String(fields["cf-turnstile-response"] || "");

  let verification;
  try {
    verification = await verifyTurnstile({
      env,
      request,
      token,
      remoteIp: getClientIp(request),
    });
  } catch (error) {
    return json({ success: false, message: TURNSTILE_ERROR_MESSAGE }, 400);
  }

  if (verification.notConfigured) {
    return json({ success: false, message: TURNSTILE_NOT_CONFIGURED_MESSAGE }, 503);
  }

  if (!verification.ok) {
    return json({
      success: false,
      message: TURNSTILE_ERROR_MESSAGE,
      turnstile: verification.result,
    }, 400);
  }

  const type = cleanField(fields.type, 80);
  const company = cleanField(fields.company, 120);
  const name = cleanField(fields.name, 80);
  const email = cleanField(fields.email, 254);
  const tel = cleanField(fields.tel, 60);
  const message = truncateText(fields.message, 3000);
  const emailIsValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

  if (!type || !company || !name || !emailIsValid) {
    return json({ success: false, message: "必須項目を確認してください。" }, 422);
  }

  const to = env.CONTACT_TO_EMAIL || "info@damaga-pro.jp";
  const from = env.CONTACT_FROM_EMAIL || "DAMAGA Pro <no-reply@damaga-pro.jp>";
  const adminSubject = "【DAMAGA Pro】お問い合わせ";
  const adminBody = [
    "DAMAGA Proサイトからお問い合わせがありました。",
    "",
    `お問い合わせ内容: ${type}`,
    `会社・施設名: ${company}`,
    `ご担当者名: ${name}`,
    `メールアドレス: ${email}`,
    `電話番号: ${tel || "未入力"}`,
    "",
    "本文:",
    message || "未入力",
  ].join("\n");

  const autoReplySubject = "【DAMAGA Pro】お問い合わせありがとうございます";
  const autoReplyBody = [
    `${name} 様`,
    "",
    "この度は、DAMAGA Proへお問い合わせいただきありがとうございます。",
    "以下の内容でお問い合わせを受け付けました。",
    "担当者より内容を確認のうえ、あらためてご連絡いたします。",
    "",
    `お問い合わせ内容: ${type}`,
    `会社・施設名: ${company}`,
    `ご担当者名: ${name}`,
    `メールアドレス: ${email}`,
    `電話番号: ${tel || "未入力"}`,
    "",
    "本文:",
    message || "未入力",
    "",
    "----------------------------------------",
    "DAMAGA Pro",
    "株式会社ファンビータ",
    "https://damaga-pro.jp/",
    "----------------------------------------",
    "",
    "※このメールは自動送信です。お心当たりがない場合は破棄してください。",
  ].join("\n");

  const adminSent = await sendResendEmail({
    env,
    to,
    from,
    replyTo: email,
    subject: adminSubject,
    text: adminBody,
  });

  if (!adminSent) {
    return json({ success: false, message: MAIL_NOT_CONFIGURED_MESSAGE }, 500);
  }

  const autoReplySent = await sendResendEmail({
    env,
    to: email,
    from,
    replyTo: to,
    subject: autoReplySubject,
    text: autoReplyBody,
  });

  if (!autoReplySent) {
    return json({ success: false, message: "自動返信メールの送信に失敗しました。時間をおいて再度お試しください。" }, 500);
  }

  return json({ success: true });
};

export const onRequest = (context) => {
  if (context.request.method === "POST") {
    return handlePost(context);
  }

  return json(
    { success: false, message: "許可されていない送信方法です。" },
    405,
    { "Allow": "POST" },
  );
};
