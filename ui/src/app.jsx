import { createRoot, hydrateRoot } from "react-dom/client";
import React, { StrictMode, Suspense } from 'react';
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { HelmetProvider } from "react-helmet-async";
import * as bootstrap from "bootstrap";

require("dotenv").config({ path: '../../.env' });

import Layout from "./layout";
import Metas from './metas';
import AuthProvider from "./providers/AuthProvider";

const Home = React.lazy(() => import('./pages/home'));
const AllServices = React.lazy(() => import('./pages/services'));
const AllOffers = React.lazy(() => import('./pages/offers'));

export default function App() {
    const currentLocation = window.location.pathname;
    const currentPath = currentLocation.substring(0, currentLocation.lastIndexOf("/")) + "/";

    return (
        <StrictMode>
            <BrowserRouter basename={currentPath}>
                <HelmetProvider>
                    <AuthProvider>
                        <Layout>
                            <Metas />
                            <Suspense fallback={<div className="spinner-border text-danger" role="status"><span className="visually-hidden">Chargement...</span></div>}>
                                <Routes>
                                    <Route exact path="/" element={<Home />} />
                                    <Route exact path="/prestations" element={<AllServices />} />
                                    <Route exact path="/occasions" element={<AllOffers />} />
                                    <Route path="*" element={<Home />} />
                                </Routes>
                            </Suspense>
                        </Layout>
                    </AuthProvider>
                </HelmetProvider>
            </BrowserRouter>
        </StrictMode>
    );
}

const rootElement = document.getElementById("app");
if (rootElement.hasChildNodes()) {
    hydrateRoot(rootElement, <App />);
} else {
    const root = createRoot(rootElement);
    root.render(<App />);
}


