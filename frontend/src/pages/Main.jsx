import { useState, useEffect, useRef } from "react";
import { useNavigate } from "react-router-dom";
import { initialLayers } from "../data/layers";
import {
  ensureSession,
  fetchStream,
  pingSession,
  pickupMessage,
  expireAssignment,
} from "../api/client";
import "./Main.css";
import nagasuIcon from "../assets/nagasu.png";
import motimonoIcon from "../assets/motimono.png";
import tukaikataIcon from "../assets/tukaikata.png";
import Wave from "../components/Wave";
import LetterScene from "../components/LetterScene";
import Bottle from "../components/Bottle";

// 流れてくるボトルの見た目（レイヤーの1つを流用）
const bottleStyle = initialLayers[3].bottle;

export default function Main() {
  const [showLetter, setShowLetter] = useState(false);
  const [letterBody, setLetterBody] = useState(null);
  // 受信して画面に流れているボトル { assignmentId, messageId, body }。null なら海に何もない。
  const [bottle, setBottle] = useState(null);
  const navigate = useNavigate();
  // StrictModeの二重実行でボトルを余分に消費しないよう、初期化を1回に限定
  const didInit = useRef(false);
  const keepAliveRef = useRef(null);
  const sessionIdRef = useRef(null);
  // pickup と expire が二重に走らないようにするガード
  const handlingRef = useRef(false);

  // 起動時: セッションを用意し、流れてきたボトルを1件取得。以後セッションを定期更新。
  useEffect(() => {
    if (!didInit.current) {
      didInit.current = true;
      (async () => {
        try {
          const sessionId = await ensureSession();
          sessionIdRef.current = sessionId;
          const data = await fetchStream(sessionId);
          if (data.message) {
            // 割り当て期限に合わせてボトルが流れきる時間を同期させる
            const remainingMs =
              new Date(data.assigned_until).getTime() - Date.now();
            const durationSec = Math.max(1, Math.round(remainingMs / 1000));
            setBottle({
              assignmentId: data.assignment_id,
              messageId: data.message.id,
              body: data.message.body,
              durationSec,
            });
          }
          // オンライン維持（30秒ごとに last_seen_at を更新）
          keepAliveRef.current = setInterval(() => {
            pingSession(sessionId).catch(() => {});
          }, 30000);
        } catch (e) {
          console.error("ボトルの取得に失敗しました", e);
        }
      })();
    }

    return () => {
      if (keepAliveRef.current) {
        clearInterval(keepAliveRef.current);
        keepAliveRef.current = null;
      }
    };
  }, []);

  // ボトルをクリック → 拾う（pickup）→ 手紙を表示
  const handlePickup = async () => {
    if (!bottle || handlingRef.current) return;
    handlingRef.current = true;
    try {
      const res = await pickupMessage(
        bottle.messageId,
        bottle.assignmentId,
        sessionIdRef.current
      );
      setLetterBody(res.body);
      setShowLetter(true);
      setBottle(null); // 拾ったので海からは消す（もちものに入る）
    } catch (e) {
      console.error("拾うのに失敗しました", e);
      setBottle(null);
    }
  };

  // ボトルが画面外へ流れ切った → 再放流（expire）
  const handleDriftOff = async () => {
    if (!bottle || handlingRef.current) return;
    handlingRef.current = true;
    try {
      await expireAssignment(bottle.assignmentId);
    } catch (e) {
      console.error("再放流に失敗しました", e);
    } finally {
      setBottle(null); // 海へ戻したので画面からも消す
    }
  };

  return (
    <div className="ocean">

    {/* 空エリア */}
    <div className="sky" />

    {/* 波レイヤー（SVGで滑らかな波形） */}
    <div className="waves-container">

    <div className="wave-layer">
      {/* 波は背景として常に表示 */}
      {initialLayers.map((layer) => (
        <Wave key={layer.id} {...layer.wave} />
      ))}

      {/* 受信したボトルがある時だけ流す。クリックで拾う、画面外で再放流。 */}
      {bottle && (
        <Bottle
          {...bottleStyle}
          duration={`${bottle.durationSec}s`}
          onClick={handlePickup}
          onDriftOff={handleDriftOff}
        />
      )}
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

      <LetterScene
        showLetter={showLetter}
        setShowLetter={setShowLetter}
        body={letterBody}
      />

    </div>
  );
}
