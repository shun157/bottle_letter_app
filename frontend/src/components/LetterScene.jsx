import "./Letter.css";

export default function LetterScene({
  showLetter,
  setShowLetter,
  buttonText = "うみにもどる",
}) {
  if (!showLetter) return null;

  return (
    <div
      className="overlay"
      onClick={(e) => e.stopPropagation()}
    >
      <div className="letter-container">
        <div className="letter">
          <div className="date">○月○日</div>

          <p>
            {body ?? "いまは海に流れているボトルがありません。"}
          </p>

          <div className="bottom-area">
            <div className="signature-line"></div>
          </div>
        </div>

        <button
          className="close-button"
          onClick={(e) => {
            e.stopPropagation();
            setShowLetter(false);
          }}
        >
          {buttonText}
        </button>
      </div>
    </div>
  );
}