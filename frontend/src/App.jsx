import { BrowserRouter, Routes, Route } from "react-router-dom";
import Main from "./pages/Main";
import Collection from "./pages/Collection";

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Main />} />
        <Route path="/collection" element={<Collection />} />
      </Routes>
    </BrowserRouter>
  );
}