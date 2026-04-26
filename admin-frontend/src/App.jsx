import { BrowserRouter as Router, Routes, Route, Navigate } from "react-router-dom";
import AdminLayout from "./components/AdminLayout";
import Dashboard from "./pages/Dashboard";
import Login from "./pages/Login";
import NewsManagement from "./pages/NewsManagement";
import EventManagement from "./pages/EventManagement";
import NewspaperManagement from "./pages/NewspaperManagement";
import BannerManagement from "./pages/BannerManagement";
import Diagnostic from "./pages/Diagnostic";
import ProtectedRoute from "./components/ProtectedRoute";
import { useAuth } from "./context/AuthContext.jsx";

function App() {
  const { isAuthenticated, loading } = useAuth();

  if (loading) {
    return <div className="text-center mt-5">Validating session...</div>;
  }

  return (
    <Router>
      <Routes>
        {/* Diagnostic route - public, no auth required */}
        <Route path="/diagnostic" element={<Diagnostic />} />

        <Route
          path="/login"
          element={
            isAuthenticated ? <Navigate to="/dashboard" replace /> : <Login />
          }
        />

        <Route
          path="/dashboard"
          element={
            <ProtectedRoute>
              <AdminLayout>
                <Dashboard />
              </AdminLayout>
            </ProtectedRoute>
          }
        />

        <Route
          path="/news"
          element={
            <ProtectedRoute>
              <AdminLayout>
                <NewsManagement />
              </AdminLayout>
            </ProtectedRoute>
          }
        />

        <Route
          path="/events"
          element={
            <ProtectedRoute>
              <AdminLayout>
                <EventManagement />
              </AdminLayout>
            </ProtectedRoute>
          }
        />

        <Route
          path="/banners"
          element={
            <ProtectedRoute>
              <AdminLayout>
                <BannerManagement />
              </AdminLayout>
            </ProtectedRoute>
          }
        />

        <Route
          path="/newspapers"
          element={
            <ProtectedRoute>
              <AdminLayout>
                <NewspaperManagement />
              </AdminLayout>
            </ProtectedRoute>
          }
        />

        <Route
          path="*"
          element={<Navigate to={isAuthenticated ? "/dashboard" : "/login"} replace />}
        />
      </Routes>
    </Router>
  );
}

export default App;
