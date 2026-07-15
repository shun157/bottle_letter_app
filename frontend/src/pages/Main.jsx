import { useState, useEffect, Fragment } from "react";
import { useNavigate } from "react-router-dom";
import { initialLayers } from "../data/layers";
import "./Main.css";
import nagasuIcon from "../assets/nagasu.png";
import motimonoIcon from "../assets/motimono.png";
import tukaikataIcon from "../assets/tukaikata.png";
import Wave from "../components/Wave";
import LetterScene from "../components/LetterScene";
import Bottle from "../components/Bottle";

export default function Main() {
  const [showLetter, setShowLetter] = useState(false);
  const [layers, setLayers] = useState(initialLayers);
  const navigate = useNavigate();
  
  useEffect(() => {
    const timer = setInterval(() => {
      setLayers(prev =>
        prev.map(layer => {
          if (layer.visible) return layer;

          return {
            ...layer,
            visible: true,
          };
        })
      );
    }, 10000);

    return () => clearInterval(timer);
  }, []);

  return (
    <div className="ocean">

    {/* 空エリア */}
    <div className="sky" />

    {/* 波レイヤー（SVGで滑らかな波形） */}
    <div className="waves-container">

    <div className="wave-layer">
      {layers.map((layer) => (
        <Fragment key={layer.id}>
          {layer.visible && (
            <Bottle
              {...layer.bottle}
              onClick={() => {
                setShowLetter(true);

                setLayers(prev =>
                  prev.map(l =>
                    l.id === layer.id
                      ? { ...l, visible: false }
                      : l
                  )
                );
              }}
            />
          )}

          <Wave {...layer.wave} />
        </Fragment>
      ))}
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
      />

    </div>
  );
}
