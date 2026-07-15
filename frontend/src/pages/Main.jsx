import { useState, useEffect, Fragment } from "react";
import { useNavigate } from "react-router-dom";
import { initialLayers } from "../data/layers";
import { ensureSession, fetchStream, pingSession } from "../api/client";
import "./Main.css";
import nagasuIcon from "../assets/nagasu.png";
import motimonoIcon from "../assets/motimono.png";
import tukaikataIcon from "../assets/tukaikata.png";
import Wave from "../components/Wave";
import Letter from "../components/Letter";
import Bottle from "../components/Bottle";

function getTodayDate() {
  const today = new Date();

  return `${today.getFullYear()}年${
    today.getMonth() + 1
  }月${today.getDate()}日`;
}

export default function Main() {
  const [showLetter, setShowLetter] = useState(false);
  
  const [letterData, setLetterData] = useState(null);

  const [layers, setLayers] = useState(initialLayers);
  // バックから取得したボトル（手紙本文）。null のときは海に流れているボトルが無い状態。
  const [letterBody, setLetterBody] = useState(null);
  const navigate = useNavigate();

  // 起動時: セッションを用意し、流れてきたボトルを1件取得。以後セッションを定期更新。
  useEffect(() => {
    let keepAlive;
    (async () => {
      try {
        const sessionId = await ensureSession();
        const data = await fetchStream(sessionId);
        setLetterBody(data.message ? data.message.body : null);
        // オンライン維持（30秒ごとに last_seen_at を更新）
        keepAlive = setInterval(() => {
          pingSession(sessionId).catch(() => {});
        }, 30000);
      } catch (e) {
        console.error("ボトルの取得に失敗しました", e);
      }
    })();

    return () => clearInterval(keepAlive);
  }, []);

  useEffect(() => {
    const timer = setInterval(() => {
      setLayers(prev =>
        prev.map(layer => {
          if (layer.visible) return layer;

          return {
            ...layer,
            visible: true,
          };
        })
      );
    }, 10000);

    return () => clearInterval(timer);
  }, []);

  return (
    <div className="ocean">

    {/* 空エリア */}
    <div className="sky" />

    {/* 波レイヤー（SVGで滑らかな波形） */}
    <div className="waves-container">

    <div className="wave-layer">
      {layers.map((layer) => (
        <Fragment key={layer.id}>
          {layer.visible && (
            <Bottle
              {...layer.bottle}
              onClick={() => {

                setLetterData({
                  title: layer.letter?.title ?? "海からの手紙",
                  date: getTodayDate(),
                  message: layer.letter?.message ?? "こんにちは。"
                });

                setShowLetter(true);

                setLayers(prev =>
                  prev.map(l =>
                    l.id === layer.id
                      ? { ...l, visible: false }
                      : l
                  )
                );
              }}
            />
          )}

          <Wave {...layer.wave} />
        </Fragment>
      ))}
    </div>
    </div>

      {/* 左メニュー */}
      <div className="menu">
        <div 
          className="menu-item"
          onClick={() => navigate("/send")}  
        >
          <img src={nagasuIcon} className="menu-icon" />
          <span>ながす</span>
        </div>

        <div 
          className="menu-item"
          onClick={() => navigate("/collection")}
        >
          <img src={motimonoIcon} className="menu-icon" />
          <span>もちもの</span>
        </div>

        <div className="menu-item">
          <img src={tukaikataIcon} className="menu-icon" />
          <span>つかいかた</span>
        </div>

      </div>

      <Letter
        showLetter={showLetter}
        setShowLetter={setShowLetter}

        title={letterData?.title}
        date={letterData?.date}
        message={letterData?.message}
      />

    </div>
  );
}
