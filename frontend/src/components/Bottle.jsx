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
}) {
  return (
    <div
      className={`bottle-float bottle-${direction}`}
      style={{
        top,
        zIndex,
        animationDuration: duration,
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