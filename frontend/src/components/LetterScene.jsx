import "./Letter.css";

export default function LetterScene({
  showLetter,
  setShowLetter,
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
            今日も一日お疲れさまでした。
            <br />
            ゆっくり休んでください。
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
          うみにもどる
        </button>
      </div>
    </div>
  );
}