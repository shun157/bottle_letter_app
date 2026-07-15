import "./Send.css";
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import bottleImg from "../assets/bottle.png";
import { ensureSession, createMessage } from "../api/client";

export default function Send() {
  const navigate = useNavigate();

  const [title, setTitle] = useState("");
  const [message, setMessage] = useState("");
  const [sending, setSending] = useState(false);

  const today = new Date();
  const date =
    `${today.getFullYear()}年${today.getMonth() + 1}月${today.getDate()}日`;

  const handleSend = async () => {
    if (title.trim() === "" || message.trim() === "") {
      alert("件名と本文を入力してください。");
      return;
    }
    if (sending) return;

    // バックエンドの本文は1フィールドのため、件名と本文をまとめて送る
    const body = `${title.trim()}\n\n${message.trim()}`;

    setSending(true);
    try {
      const sessionId = await ensureSession();
      await createMessage(sessionId, body);
      alert("メッセージを海へ流しました！");
      navigate("/");
    } catch (e) {
      console.error("放流に失敗しました", e);
      alert("流すのに失敗しました。もう一度お試しください。");
    } finally {
      setSending(false);
    }
  };

  return (
    <div className="send-page">

      <button
        className="back-btn"
        onClick={() => navigate("/")}
      >
        ← うみへ
      </button>

      <div className="letter">

        <input
          className="title-input"
          placeholder="件名"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
        />

        <div className="date">
          {date}
        </div>

        <textarea
            className="message-input"
            placeholder="メッセージを書いてください"
            value={message}
            onChange={(e) => setMessage(e.target.value)}
        />

        <button
          className="send-button"
          onClick={handleSend}
          disabled={sending}
        >
          <img src={bottleImg} alt="瓶" className="button-bottle"/>
          <span>{sending ? "ながしています…" : "うみにながす"}</span>
        </button>

      </div>

    </div>
  );
}