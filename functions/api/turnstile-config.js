export const onRequest = ({ request, env }) => {
  if (request.method !== "GET") {
    return Response.json(
      { siteKey: "" },
      {
        status: 405,
        headers: {
          "Allow": "GET",
          "Cache-Control": "no-store",
        },
      },
    );
  }

  return Response.json(
    { siteKey: env.NEXT_PUBLIC_TURNSTILE_SITE_KEY || "" },
    {
      headers: {
        "Cache-Control": "no-store",
      },
    },
  );
};
