import React from "react";

import Header from "./header";
import Footer from "./footer";

export default function Layout({ children }) {
    return (
        <>
            <Header />
            <main className="container-lg px-3" style={{ paddingBottom: "8rem" }}>
                {children}
            </main>
            <Footer style={{ position: "absolute", width: "100%", bottom: "0", height: "8rem" }} />
        </>
    );
}