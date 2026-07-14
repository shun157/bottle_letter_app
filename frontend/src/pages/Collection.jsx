import "./Collection.css";
import bottleImg from "../assets/bottle.png";
import { useNavigate } from "react-router-dom";

export default function Collection() {
  const navigate = useNavigate();

  // 仮データ
  const bottles = [1, 2, 3, 4, 5, 6, 7];

  // 5個ずつ棚に分ける
  const shelves = Array.from(
    { length: Math.ceil(bottles.length / 5) },
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
            {shelf.map((id) => (
              <img
                key={id}
                src={bottleImg}
                alt="ボトル"
                className="collection-bottle"
              />
            ))}
          </div>

          <div className="shelf"></div>
        </div>
      ))}
    </div>
  );
}