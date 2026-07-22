// バックエンド(Laravel)APIクライアント
// Vite の dev proxy 経由で /api を叩くため、baseURL は付けない（同一オリジン扱い）。

const SESSION_KEY = "bottle_session_id";

async function request(path, options = {}) {
  const res = await fetch(`/api${path}`, {
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    ...options,
  });
  if (!res.ok) {
    const err = new Error(`API ${path} failed: ${res.status}`);
    err.status = res.status;
    throw err;
  }
  return res.json();
}

// セッション発行の多重実行を防ぐための in-flight プロミス
let sessionPromise = null;

// 新しいセッションを発行して localStorage に保存（発行リクエストは1回にまとめる）
function createSession() {
  if (!sessionPromise) {
    sessionPromise = request("/sessions", { method: "POST" })
      .then((data) => {
        localStorage.setItem(SESSION_KEY, data.session_id);
        return data.session_id;
      })
      .finally(() => {
        sessionPromise = null;
      });
  }
  return sessionPromise;
}

// セッションを取得する。
// 保存済みのセッションが（DBリセット等で）無効になっていたら破棄して作り直す。
export async function ensureSession() {
  const stored = localStorage.getItem(SESSION_KEY);
  if (stored) {
    try {
      // last_seen_at 更新を兼ねて有効性を確認（無効なら422）
      await pingSession(stored);
      return stored;
    } catch (e) {
      if (e.status === 422) {
        localStorage.removeItem(SESSION_KEY); // 無効なので破棄 → 新規発行へ
      } else {
        return stored; // 一時的な通信エラー等では保存済みIDを維持
      }
    }
  }
  return createSession();
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

// ボトルを拾う（回収）
export async function pickupMessage(messageId, assignmentId, sessionId) {
  return request(`/messages/${messageId}/pickup`, {
    method: "POST",
    body: JSON.stringify({
      assignment_id: assignmentId,
      receiver_session_id: sessionId,
    }),
  });
}

// 割り当てを期限切れにして海へ戻す（再放流）
export async function expireAssignment(assignmentId) {
  return request(`/assignments/${assignmentId}/expire`, { method: "PATCH" });
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
