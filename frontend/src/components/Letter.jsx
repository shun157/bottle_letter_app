import "./Letter.css";

export default function Letter({
  showLetter,
  setShowLetter,
  buttonText = "うみにもどる",

  title = "件名",
  date = "2026年7月15日",
  message = "",
}) {
  if (!showLetter) return null;

  return (
    <div
      className="overlay"
      onClick={() => setShowLetter(false)}
    >
      <div
        className="letter"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="letter-title">
          {title}
        </div>

        <div className="date">
          {date}
        </div>

        <div className="message-view">
          {message.split("\n").map((line, index) => (
            <p key={index}>{line}</p>
          ))}
        </div>

        <button
          className="close-button"
          onClick={() => setShowLetter(false)}
        >
          {buttonText}
        </button>
      </div>
    </div>
  );
}