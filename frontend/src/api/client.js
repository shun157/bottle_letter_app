// バックエンド(Laravel)APIクライアント
// Vite の dev proxy 経由で /api を叩くため、baseURL は付けない（同一オリジン扱い）。

const SESSION_KEY = "bottle_session_id";

async function request(path, options = {}) {
  const res = await fetch(`/api${path}`, {
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    ...options,
  });
  if (!res.ok) {
    throw new Error(`API ${path} failed: ${res.status}`);
  }
  return res.json();
}

// セッションを取得（無ければ発行して localStorage に保存）
export async function ensureSession() {
  let sessionId = localStorage.getItem(SESSION_KEY);
  if (sessionId) return sessionId;

  const data = await request("/sessions", { method: "POST" });
  sessionId = data.session_id;
  localStorage.setItem(SESSION_KEY, sessionId);
  return sessionId;
}

// 自分の画面に流すべきボトルを1件取得（無ければ message:null）
export async function fetchStream(sessionId) {
  return request(`/stream?session_id=${encodeURIComponent(sessionId)}`);
}
