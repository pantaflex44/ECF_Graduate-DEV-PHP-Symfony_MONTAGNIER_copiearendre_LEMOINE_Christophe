import { createRoot, hydrateRoot } from "react-dom/client";
import React, { Suspense } from 'react';
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { HelmetProvider } from "react-helmet-async";
import * as bootstrap from "bootstrap";

require("dotenv").config({ path: '../../.env' });

import Layout from "./layout";
import Metas from './metas';
const Home = React.lazy(() => import('./pages/home'));

export default function App() {
    const currentLocation = window.location.pathname;
    const currentPath = currentLocation.substring(0, currentLocation.lastIndexOf("/")) + "/";

    return (
        <BrowserRouter basename={currentPath}>
            <HelmetProvider>
                <Layout>
                    <Metas />
                    <Suspense fallback={<div className="spinner-border text-danger" role="status"><span className="visually-hidden">Chargement...</span></div>}>
                        <Routes>
                            <Route exact path="/" element={<Home />} />
                        </Routes>
                    </Suspense>
                </Layout>
            </HelmetProvider>
        </BrowserRouter>
    );
}

const rootElement = document.getElementById("app");
if (rootElement.hasChildNodes()) {
    hydrateRoot(rootElement, <App />);
} else {
    const root = createRoot(rootElement);
    root.render(<App />);
}


