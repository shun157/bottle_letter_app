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

// 手紙を海に流す（放流）
export async function createMessage(sessionId, body) {
  return request("/messages", {
    method: "POST",
    body: JSON.stringify({ body, sender_session_id: sessionId }),
  });
}

// コレクション（拾った手紙・流した手紙の履歴）を取得
export async function fetchCollection(sessionId) {
  return request(`/collection?session_id=${encodeURIComponent(sessionId)}`);
}

// セッションのオンライン状態を維持（last_seen_at 更新）
export async function pingSession(sessionId) {
  return request("/session/active", {
    method: "PUT",
    body: JSON.stringify({ session_id: sessionId }),
  });
}
