import "./Send.css";
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import bottleImg from "../assets/bottle.png";

export default function Send() {
  const navigate = useNavigate();

  const [title, setTitle] = useState("");
  const [message, setMessage] = useState("");

  const today = new Date();
  const date =
    `${today.getFullYear()}年${today.getMonth() + 1}月${today.getDate()}日`;

  const handleSend = () => {
    if (title.trim() === "" || message.trim() === "") {
      alert("件名と本文を入力してください。");
      return;
    }

    // 今は確認だけ
    console.log({
      title,
      message,
      date,
    });

    alert("メッセージを海へ流しました！");

    navigate("/");
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
        >
          <img src={bottleImg} alt="瓶" className="button-bottle"/>
          <span>うみにながす</span>
        </button>

      </div>

    </div>
  );
}