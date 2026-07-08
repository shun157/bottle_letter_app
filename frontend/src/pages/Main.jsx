import { useState, useEffect, Fragment } from "react";
import { initialLayers } from "../data/layers";
import { ensureSession, fetchStream } from "../api/client";
import "./Main.css";
import nagasuIcon from "../assets/nagasu.png";
import motimonoIcon from "../assets/motimono.png";
import tukaikataIcon from "../assets/tukaikata.png";
import Wave from "../components/Wave";
import LetterScene from "../components/LetterScene";
import Bottle from "../components/Bottle";

export default function Main() {
  const [showLetter, setShowLetter] = useState(false);
  const [layers, setLayers] = useState(initialLayers);
  // バックから取得したボトル（手紙本文）。null のときは海に流れているボトルが無い状態。
  const [letterBody, setLetterBody] = useState(null);

  // 起動時にセッションを用意し、流れてきたボトルを1件取得する
  useEffect(() => {
    (async () => {
      try {
        const sessionId = await ensureSession();
        const data = await fetchStream(sessionId);
        setLetterBody(data.message ? data.message.body : null);
      } catch (e) {
        console.error("ボトルの取得に失敗しました", e);
      }
    })();
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
        <div className="menu-item">うみへ</div>

        <div className="menu-item">
          <img src={nagasuIcon} className="menu-icon" />
          <span>ながす</span>
        </div>

        <div className="menu-item">
          <img src={motimonoIcon} className="menu-icon" />
          <span>もちもの</span>
        </div>

        <div className="menu-item">
          <img src={tukaikataIcon} className="menu-icon" />
          <span>つかいかた</span>
        </div>

      </div>

      <LetterScene
        showLetter={showLetter}
        setShowLetter={setShowLetter}
        body={letterBody}
      />

    </div>
  );
}
