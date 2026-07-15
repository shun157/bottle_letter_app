import bottleImg from "../assets/bottle.png";
import "./Bottle.css";

export default function Bottle({
  top,
  direction,
  duration,
  width,
  opacity,
  angle,
  zIndex,
  onClick,
  onDriftOff,
}) {
  return (
    <div
      className={`bottle-float bottle-${direction}`}
      style={{
        top,
        zIndex,
        animationDuration: duration,
      }}
      onAnimationIteration={(e) => {
        // 画面を1周流れ切った（＝画面外へ消えた）タイミング。
        // 内側の瓶の揺れ(rock)アニメーションとは区別する。
        if (e.animationName === "driftRight" || e.animationName === "driftLeft") {
          onDriftOff?.();
        }
      }}
    >
      <img
        src={bottleImg}
        alt="瓶"
        className="bottle"
        style={{
          width,
          opacity,
          "--angle": `${angle}deg`,
        }}
        onClick={onClick}
      />
    </div>
  );
}