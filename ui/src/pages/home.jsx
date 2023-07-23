import React from "react";

import OpeningHours from "../components/OpeningHours";
import PhoneButton from "../components/PhoneButton";

import Gvp1 from "../assets/gvp1.jpg";
import Map1 from "../assets/map1.png";
import ImgCompetences from "../assets/competences.png";
import ImgPieces from "../assets/pieces.png";
import ImgChoix from "../assets/choix.png";
import ImgParebrise from "../assets/parebrise.png";
import ImgFormalites from "../assets/formalites.png";
import ImgGarantie from "../assets/garantie.png";
import Promo1 from "../assets/promo1.avif";
import Promo2 from "../assets/promo2.avif";
import Promo3 from "../assets/promo3.avif";
import Promo4 from "../assets/promo4.avif";
import Promo5 from "../assets/promo5.avif";

import ScrollToTop from "../components/ScrollToTop";
import MessageButton from "../components/MessageButton";
import Services from "../components/Services";
import Offers from "../components/Offers";


export default function Home() {
    return <>
        <ScrollToTop />

        {/* COORDONNEES */}
        <div className="row align-items-stretch g-0 mt-5 justify-content-center h-100">
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
                <p className="fs-6 text-uppercase fw-bolder">Horaires d'ouverture</p>
                <OpeningHours />
            </div>
            <div className="col-12 col-sm-6 col-lg-2 d-none d-lg-block">
                <img className="object-fit-cover" src={Map1} style={{ width: "100%", minHeight: "100%", maxHeight: "216px" }} />
            </div>
        </div>

        <div className="row align-items-stretch justify-content-center mt-5 mb-5 mx-1" style={{gap: "2em", maxWidth: "100%"}}>

            {/* ENGAGEMENTS */}
            <div className="col order-1 order-md-0 bg-body-tertiary pt-3" style={{ minWidth: "330px", maxWidth: "100%" }}>
                <div className="bottom-arrow-gray">
                    <p className="h4 text-center text-uppercase fw-bold my-2 mx-0">Nos engagements</p>
                </div>
                <div className="row align-items-stretch justify-content-center mt-0 p-0 pt-5">
                    <div className="col col-md-4 justify-content-center text-center pt-4 pb-2">
                        <img src={ImgCompetences} alt="Compétences Techniques Multimarques" width={72} height={72} />
                        <p className="mt-3 small">Compétences Techniques Multimarques</p>
                    </div>
                    <div className="col col-md-4 justify-content-center text-center pt-4 pb-2">
                        <img src={ImgPieces} alt="Garantie Pièces d'Origine" width={72} height={72} />
                        <p className="mt-3 small">Garantie Pièces d'Origine</p>
                    </div>
                    <div className="col col-md-4 justify-content-center text-center pt-4 pb-2">
                        <img src={ImgChoix} alt="Libre Choix du Mécanicien" width={72} height={72} />
                        <p className="mt-3 small">Libre Choix du Mécanicien</p>
                    </div>
                    <div className="col col-md-4 justify-content-center text-center pt-4 pb-2">
                        <img src={ImgParebrise} alt="Spécialité Pare-Brise" width={72} height={72} />
                        <p className="mt-3 small">Spécialité Pare-Brise</p>
                    </div>
                    <div className="col col-md-4 justify-content-center text-center pt-4 pb-2">
                        <img src={ImgFormalites} alt="Prise en Charge des Formalités Administratives" width={72} height={72} />
                        <p className="mt-3 small">Prise en Charge des Formalités Administratives</p>
                    </div>
                    <div className="col col-md-4 justify-content-center text-center pt-4 pb-2">
                        <img src={ImgGarantie} alt="Garantie Constructeur Préservée" width={72} height={72} />
                        <p className="mt-3 small">Garantie Constructeur Préservée</p>
                    </div>
                </div>
            </div>

            {/* PROMOTIONS */}
            <div id="comm-window" className="col order-0 order-md-1 carousel carousel-dark slide m-0 p-0" data-bs-ride="carousel">
                <div className="carousel-indicators">
                    <button type="button" data-bs-target="#comm-window" data-bs-slide-to="0" className="active" aria-current="true" aria-label="Promotion 1"></button>
                    <button type="button" data-bs-target="#comm-window" data-bs-slide-to="1" aria-label="Promotion 2"></button>
                    <button type="button" data-bs-target="#comm-window" data-bs-slide-to="2" aria-label="Promotion 3"></button>
                    <button type="button" data-bs-target="#comm-window" data-bs-slide-to="3" aria-label="Promotion 4"></button>
                    <button type="button" data-bs-target="#comm-window" data-bs-slide-to="4" aria-label="Promotion 5"></button>
                </div>
                <div className="carousel-inner border border-light-subtle" style={{ width: "100%", minWidth: "330px", maxWidth: "100%" }}>
                    <div className="carousel-item active">
                        <img src={Promo1} className="d-block w-100" alt="Promotion" style={{ minWidth: "330px", maxWidth: "100%" }} />
                    </div>
                    <div className="carousel-item">
                        <img src={Promo2} className="d-block w-100" alt="Promotion" style={{ minWidth: "330px", maxWidth: "100%" }} />
                    </div>
                    <div className="carousel-item">
                        <img src={Promo3} className="d-block w-100" alt="Promotion" style={{ minWidth: "330px", maxWidth: "100%" }} />
                    </div>
                    <div className="carousel-item">
                        <img src={Promo4} className="d-block w-100" alt="Promotion" style={{ minWidth: "330px", maxWidth: "100%" }} />
                    </div>
                    <div className="carousel-item">
                        <img src={Promo5} className="d-block w-100" alt="Promotion" style={{ minWidth: "330px", maxWidth: "100%" }} />
                    </div>
                </div>
            </div>

        </div>

        {/* PRESTATIONS */}
        <div className="row mt-5">
            <div className="col-12 mt-2 border-bottom border-light-subtle">
                <p className="h3 text-uppercase fw-bold">Nos prestations</p>
                <p className="text-danger lh-sm">
                    <small>Nos mécaniciens s’occupent de la réparation et l’entretien de votre voiture, peu importe la marque ou le modèle de celle-ci. Profitez d’une prestation de qualité effectuée par des véritables experts auto. Demandez nous dès maintenant un devis pour la réparation de votre voiture et obtenez un RDV dans les plus brefs délais!</small>
                </p>
            </div>
            <div className="col-12">
                <Services preview={true} />
            </div>
        </div>

        {/* OCCASIONS */}
        <div className="row">
            <div className="col-12 mt-2 border-bottom border-light-subtle">
                <p className="h3 text-danger fw-bold text-uppercase mt-3 mb-3">⇀ NOUVEAU !</p>
                <p className="h4 text-uppercase"><span className="fw-bold">VÉHICULES D’OCCASION CERTIFIÉS</span> <span className="fw-light">DISPONIBLES DANS NOTRE GARAGE !</span></p>
                <p className="h6 fw-bold">Profitez de notre expertise pour trouver le véhicule d’occasion qui vous correspond.</p>
            </div>
            <div className="col-12">
                <Offers preview={true} />
            </div>
        </div>

    </>;
}