import "./Collection.css";
import bottleImg from "../assets/bottle.png";
import Letter from "../components/Letter";
import { useState } from "react";
import { useNavigate } from "react-router-dom";

export default function Collection() {
  const navigate = useNavigate();

  const [showLetter, setShowLetter] = useState(false);
  const [selectedBottle, setSelectedBottle] = useState(null);

  // 拾った手紙の一覧の仮データ
  const bottles = [
    {
      id: 1,
      title: "今日の出来事",
      date: "2026年7月15日",
      message: "今日はいいことがありました。",
    },
    {
      id: 2,
      title: "ありがとう",
      date: "2026年7月14日",
      message: "助けてくれてありがとう！",
    },
    {
      id: 3,
      title: "ひとりごと",
      date: "2026年7月13日",
      message: "眠たいです。",
    },
  ];

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
            {shelf.map((bottle) => (
              <img
                key={bottle.id}
                src={bottleImg}
                alt="ボトル"
                className="collection-bottle"
                onClick={() => {
                  setSelectedBottle(bottle);
                  setShowLetter(true);
                }}
              />
            ))}
          </div>

          <div className="shelf"></div>
        </div>
      ))}
      <Letter
        showLetter={showLetter}
        setShowLetter={setShowLetter}
        title={selectedBottle?.title}
        date={selectedBottle?.date}
        message={selectedBottle?.message}
        buttonText="もちものにもどる"
      />
    </div>
  );
}