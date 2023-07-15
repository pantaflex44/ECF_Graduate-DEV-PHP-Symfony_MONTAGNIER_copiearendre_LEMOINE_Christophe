import React, { useEffect, useState } from "react";

import LogoFull from "./assets/logo_full.png";

import Header from "./header";

export default function Layout({ children }) {
    return (
        <>
            <Header />
            <main>
                {children}
            </main>
            <footer></footer>
        </>
    );
}