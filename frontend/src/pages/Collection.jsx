import "./Collection.css";
import bottleImg from "../assets/bottle.png";
import LetterScene from "../components/LetterScene";
import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { ensureSession, fetchCollection } from "../api/client";

export default function Collection() {
  const navigate = useNavigate();

  const [showLetter, setShowLetter] = useState(false);
  const [selectedBody, setSelectedBody] = useState(null);
  // 拾った手紙の一覧（もちもの）
  const [bottles, setBottles] = useState([]);

  // 開いたときに拾った手紙を取得
  useEffect(() => {
    (async () => {
      try {
        const sessionId = await ensureSession();
        const data = await fetchCollection(sessionId);
        setBottles(data.picked_messages ?? []);
      } catch (e) {
        console.error("コレクションの取得に失敗しました", e);
      }
    })();
  }, []);

  // 5個ずつ棚に分ける
  const shelves = Array.from(
    { length: Math.max(1, Math.ceil(bottles.length / 5)) },
    (_, i) => bottles.slice(i * 5, i * 5 + 5)
  );

  return (
    <div className="collection">
      <button className="back-btn" onClick={() => navigate("/")}>
        ← うみへ
      </button>

      {shelves.map((shelf, index) => (
        <div className="shelf-wrapper" key={index}>
          <div className="bottles">
            {shelf.map((item) => (
              <img
                key={item.pickup_id}
                src={bottleImg}
                alt="ボトル"
                className="collection-bottle"
                onClick={() => {
                  setSelectedBody(item.body);
                  setShowLetter(true);
                }}
              />
            ))}
          </div>

          <div className="shelf"></div>
        </div>
      ))}
      <LetterScene
        showLetter={showLetter}
        setShowLetter={setShowLetter}
        body={selectedBody}
        buttonText="もどる"
      />
    </div>
  );
}