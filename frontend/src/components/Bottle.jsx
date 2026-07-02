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
    <img
      src={bottleImg}
      alt="瓶"
      className={`bottle bottle-${direction}`}
      style={{
        top,
        width,
        opacity,
        zIndex,
        animationDuration: duration,
        "--angle": `${angle ?? 25}deg`,
      }}
      onClick={onClick}
    />
  );
}