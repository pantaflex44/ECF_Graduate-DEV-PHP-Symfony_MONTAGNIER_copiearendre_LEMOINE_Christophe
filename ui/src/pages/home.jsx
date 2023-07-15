import React from "react";

import OpeningHours from "../components/OpeningHours";
import PhoneButton from "../components/PhoneButton";

import Gvp1 from "../assets/gvp1.jpg";
import Map1 from "../assets/map1.png";
import MessageButton from "../components/MessageButton";


export default function Home() {
    return <>
        <div className="row align-items-stretch g-0 mt-3 justify-content-center h-100">
            <div className="col-12 col-sm-12 col-lg-2">
                <img className="object-fit-cover" src={Gvp1} style={{ width: "100%", minHeight: "100%", maxHeight: "216px" }} />
            </div>
            <div className="col-12 col-sm-6 col-lg-4 bg-body-tertiary p-4">
                <p className="fs-3 text-uppercase fw-bolder">{process.env.SITENAME}</p>
                <p className="fs-6 text-uppercase">{process.env.POSTAL_ADDRESS}</p>
                <div className="py-2"><PhoneButton phoneNumber={process.env.PHONE} /></div>
                <div className="pt-2"><MessageButton text="Nous contacter" /></div>
            </div>
            <div className="col-12 col-sm-6 col-lg-4 bg-body-tertiary p-4">
                <p className="fs-5 text-uppercase fw-bolder">Horaires d'ouverture</p>
                <OpeningHours />
            </div>
            <div className="col-12 col-sm-6 col-lg-2 d-none d-lg-block">
                <img className="object-fit-cover" src={Map1} style={{ width: "100%", minHeight: "100%", maxHeight: "216px" }} />
            </div>
        </div>


    </>;
}