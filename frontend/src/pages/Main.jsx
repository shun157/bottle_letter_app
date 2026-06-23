import "./Main.css";
import nagasuIcon from "../assets/nagasu.png";
import motimonoIcon from "../assets/motimono.png";
import tukaikataIcon from "../assets/tukaikata.png";


export default function Main() {
  return (
    <div className="main-bg">
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
    </div>
  );
}

