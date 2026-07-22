import "./Main.css";
import nagasuIcon from "../assets/nagasu.png";
import motimonoIcon from "../assets/motimono.png";
// import tukaikataIcon from "../assets/tukaikata.png";


export default function Main() {
  return (
    <div className="ocean">

      {/* 空エリア */}
      <div className="sky" />

      {/* 波レイヤー（SVGで滑らかな波形） */}
      <div className="waves-container">
        {/* 一番奥・薄い（画面上部） */}
  <div className="wave-track" style={{ top: '15%', height: '18%', animationDuration: '18s', opacity: 0.3 }}>
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
      <path d="M0,60 C200,20 400,100 600,60 C800,20 1000,100 1200,60 C1320,40 1400,70 1440,60 L1440,120 L0,120 Z" fill="#a8d8f0" />
    </svg>
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
      <path d="M0,60 C200,20 400,100 600,60 C800,20 1000,100 1200,60 C1320,40 1400,70 1440,60 L1440,120 L0,120 Z" fill="#a8d8f0" />
    </svg>
  </div>

  {/* 奥から2番目 */}
  <div className="wave-track" style={{ top: '25%', height: '20%', animationDuration: '14s', animationDirection: 'reverse', opacity: 0.4 }}>
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
      <path d="M0,50 C300,10 600,90 900,50 C1100,20 1300,80 1440,50 L1440,120 L0,120 Z" fill="#7ec8e3" />
    </svg>
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
      <path d="M0,50 C300,10 600,90 900,50 C1100,20 1300,80 1440,50 L1440,120 L0,120 Z" fill="#7ec8e3" />
    </svg>
  </div>

  {/* 中間 */}
  <div className="wave-track" style={{ top: '38%', height: '22%', animationDuration: '11s', opacity: 0.55 }}>
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
      <path d="M0,55 C250,15 500,95 750,55 C1000,15 1200,85 1440,55 L1440,120 L0,120 Z" fill="#4a9fd4" />
    </svg>
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
      <path d="M0,55 C250,15 500,95 750,55 C1000,15 1200,85 1440,55 L1440,120 L0,120 Z" fill="#4a9fd4" />
    </svg>
  </div>

  {/* 手前から2番目 */}
  <div className="wave-track" style={{ top: '52%', height: '25%', animationDuration: '9s', animationDirection: 'reverse', opacity: 0.7 }}>
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
      <path d="M0,45 C180,5 360,85 540,45 C720,5 900,85 1080,45 C1260,5 1380,65 1440,45 L1440,120 L0,120 Z" fill="#1e7fc4" />
    </svg>
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
      <path d="M0,45 C180,5 360,85 540,45 C720,5 900,85 1080,45 C1260,5 1380,65 1440,45 L1440,120 L0,120 Z" fill="#1e7fc4" />
    </svg>
  </div>

  {/* 一番手前・濃い（画面下部） */}
  <div className="wave-track" style={{ top: '65%', height: '38%', animationDuration: '7s', opacity: 1 }}>
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
      <path d="M0,40 C150,0 300,80 450,40 C600,0 750,80 900,40 C1050,0 1200,70 1440,40 L1440,120 L0,120 Z" fill="#0e5fa0" />
    </svg>
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
      <path d="M0,40 C150,0 300,80 450,40 C600,0 750,80 900,40 C1050,0 1200,70 1440,40 L1440,120 L0,120 Z" fill="#0e5fa0" />
    </svg>
  </div>

</div>

      /* 左メニュー */
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

        {/* <div className="menu-item">
          <img src={tukaikataIcon} className="menu-icon" />
          <span>つかいかた</span>
        </div> */}

      </div>
    </div>
  );
}

