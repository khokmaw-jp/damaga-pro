export default function handler(req, res) {
  res.setHeader("Cache-Control", "no-store");
  res.setHeader("Content-Type", "application/json; charset=utf-8");

  if (req.method !== "GET") {
    res.setHeader("Allow", "GET");
    return res.status(405).json({ siteKey: "" });
  }

  return res.status(200).json({
    siteKey: process.env.NEXT_PUBLIC_TURNSTILE_SITE_KEY || "",
  });
}
