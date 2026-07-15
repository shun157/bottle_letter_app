import "./Wave.css";

export default function Wave({
  top,
  height,
  duration,
  opacity,
  zIndex,
  reverse,
  fill,
  path,
}) {
  return (
    <div
      className="wave-track"
      style={{
        top,
        height,
        animationDuration: duration,
        animationDirection: reverse ? "reverse" : "normal",
        opacity,
        zIndex,
      }}
    >
      <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
        <path d={path} fill={fill} />
      </svg>

      <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
        <path d={path} fill={fill} />
      </svg>
    </div>
  );
}