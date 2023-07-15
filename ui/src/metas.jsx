import React from "react";
import { Helmet } from "react-helmet-async";

import Logo from "./assets/logo.png";

export default function Metas() {
    return (
        <Helmet htmlAttributes={{ lang: process.env.LANG }}>
            <title>{process.env.TITLE}</title>
            <meta name="description" content={process.env.DESCRIPTION} />
            <meta name="keywords" content={process.env.KEYWORDS} />
            <meta http-equiv="content-language" content={process.env.LANG} />
            <meta name="author" content="Christophe LEMOINE" />
            <meta name="generator" content="ParcelJs" />
            <meta name="publisher" content={process.env.PUBLISHER} />
            <meta property="og:site_name" content={process.env.TITLE} />
            <meta property="og:title" content={process.env.TITLE} />
            <meta property="og:description" content={process.env.DESCRIPTION} />
            <meta property="og:image" content={Logo} />
            <meta property="og:url" content={window.location.href} />
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:image:alt" content={Logo} />
            <link rel="icon" type="image/x-icon" href={Logo} />
            <meta name="robots" content="all" />
            <meta property="place:location:latitude" content={process.env.LATITUDE} />
            <meta property="place:location:longitude" content={process.env.LONGITUDE} />
        </Helmet>
    );
}