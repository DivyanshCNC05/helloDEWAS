import React from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext.jsx";

export default function Header({ toggleSidebar }) {
  const navigate = useNavigate();
  const { logout } = useAuth();

  return (
    <nav className="navbar navbar-light bg-light border-bottom px-3 shadow-sm d-flex justify-content-between align-items-center">
      <div className="d-flex align-items-center">
        {/* Sidebar toggle button (only visible on small screens) */}
        <button
          className="btn btn-outline-secondary btn-sm me-2 d-md-none"
          onClick={toggleSidebar}
        >
          ☰
        </button>
        <span className="navbar-brand mb-0 h4 text-primary">📰 News Admin Panel</span>
      </div>

      <div>
        <button
          className="btn btn-danger btn-sm"
          onClick={() => {
            logout();
            navigate("/login", { replace: true });
          }}
        >
          Logout
        </button>
      </div>
    </nav>
  );
}
