import React, { Fragment, useContext, useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import axios from 'axios';

import Blank from "../assets/blank.jpg";

import { AuthContext } from "../providers/AuthProvider";

import Alert from "./Alert";
import MessageButton from "./MessageButton";


export default function Offers({ preview = true }) {
    const auth = useContext(AuthContext);
    const navigate = useNavigate();

    let mounted = false;

    const defaultState = { error: null, list: [], page: 1, perPage: 1, totalPage: 0, offset: 0, count: 0, start: 1, end: 1, paginator: 1 };
    const [offers, setOffers] = useState({ ...defaultState });
    const [filters, setFilters] = useState({});
    const [loading, setLoading] = useState(false);
    const [showDetails, setShowDetails] = useState('');

    async function getOffers(currentPage = 1) {
        if (loading) return;
        setLoading(true);

        const data = preview ? undefined : filters;
        const options = auth.jwt.token ? { headers: { Authorization: `Bearer ${auth.jwt.token}` } } : undefined;

        axios.post(process.env.API_ENDPOINT + '/offers/' + currentPage, data, options)
            .then(response => {
                if (mounted && response?.status === 200) {
                    let list = response.data.data;
                    let page = response.data.page;
                    let perPage = response.data.per_page;
                    let totalPage = response.data.total_page;
                    let offset = response.data.offset;
                    let count = response.data.count;
                    if (preview) {
                        list = list.slice(0, count >= 4 ? 4 : count)
                        page = 1;
                        perPage = list.length;
                        totalPage = 1;
                        offset = 0;
                        count = list.length
                    }
                    let start = page <= 4 ? 1 : page - 2;
                    if (start < 1) start = 1;
                    if (start > totalPage) start = totalPage;
                    let end = start + 4;
                    if (end < 1) start = 1;
                    if (end > totalPage) end = totalPage;

                    setOffers(old => {
                        return {
                            ...old,
                            error: null,
                            list,
                            page,
                            perPage,
                            totalPage,
                            offset,
                            count,
                            start,
                            end,
                            paginator: end - start > 0 ? end - start : 1
                        }
                    });


                }
            })
            .catch(ex => {
                let message = ex.message;
                switch (ex.request?.status) {
                    case 500:
                        message = "Une erreur interne s'est produite!";
                        break;
                }
                const code = ex.request?.status ?? 500;
                setOffers(old => {
                    return {
                        ...old,
                        ...defaultState,
                        error: `(#${code}) ${message}`,
                    }
                });
            })
            .finally(() => {
                setLoading(false);

                if (window) window.scrollTo(0, 0);
            });
    }

    useEffect(() => {
        mounted = true;
        getOffers();
        return (() => { mounted = false; })
    }, [auth.jwt.token]);

    function show(data) {
        setShowDetails(`offer-details-${data.id}`);
    }

    function hide(data) {
        setShowDetails('');
    }

    return (
        <>
            {offers.error
                ? <Alert title="Impossible de récupérer la liste des véhicules!" message={offers.error} />
                : <div className="mb-5">
                    {loading
                        ? <div className="d-flex justify-content-center">
                            <div className="spinner-border spinner-border-sm text-danger mt-5 mx-auto" role="status">
                                <span className="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                        : <>
                            {!preview && <>
                                <div className="small text-body text-uppercase text-end mt-2 mb-5"><small>Page {offers.page}/{offers.totalPage} <span className="text-danger mx-1">●</span> {offers.count} annonce{offers.count > 1 && 's'} trouvée{offers.count > 1 && 's'}</small></div>
                            </>}
                            <div className="row row-cols-1 row-cols-lg-2 g-4 mt-3">
                                {offers.list.map((entry, i) => {
                                    return <Fragment key={`card-${entry.id}`}>
                                        <div className="col" key={`offer-${entry.id}`}>
                                            <div className={`card h-100 rounded-0 ${preview ? (entry.active === 0 ? 'border border-light-subtle' : 'border-0') : 'border-0'}`.trim()} aria-disabled={entry.active === 0} style={{ minHeight: "180px", cursor: 'pointer' }} onClick={() => show(entry)} >
                                                <div className="row g-0">
                                                    <div className="col-md-4 d-flex flex-column justify-content-start position-relative">
                                                        <img src={entry.gallery && entry.gallery.length > 0 ? entry.gallery[0] : Blank} className="rounded-0 w-100 h-100 object-fit-cover" style={{ minWidth: "138px", maxHeight: "180px", objectPosition: "50% 40%", filter: entry.active === 0 ? 'grayscale(1) opacity(0.5)' : null }} alt={entry.name} />
                                                        {!preview && <span className="position-absolute top-0 translate-middle badge rounded-pill bg-danger border border-white" style={{ left: "50%", transform: "translateX(-50%)" }}>
                                                            {entry.gallery.length} photo{entry.gallery.length > 1 ? 's' : ''}
                                                            <span className="visually-hidden">{entry.gallery.length} photo{entry.gallery.length > 1 ? 's' : ''}</span>
                                                        </span>}
                                                    </div>
                                                    <div className="col-md-8">
                                                        <div className={`card-body ${entry.active === 0 ? 'bg-body text-body-tertiary' : 'bg-body-tertiary text-body'}`.trim()} style={{ minHeight: "180px" }}>
                                                            <h5 className={`card-title text-uppercase fw-bold m-0 text-truncate`.trim()}>{entry.name}</h5>
                                                            {entry.informations?.model && <p className="m-0 text-uppercase fw-light">{entry.informations?.model}</p>}
                                                            <h6 className="card-subtitle mt-3 mb-2 small">{(new Date(parseInt(entry.release_date.substr(0, 4)), parseInt(entry.release_date.substr(5, 2)) - 1)).toLocaleDateString(process.env.LANG, { year: 'numeric', month: 'long' })} <span className="text-danger mx-1">●</span> <span className="fw-bold">{entry.mileage.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ")} km</span> <span className="text-danger mx-1">●</span> {entry.informations.fuel ?? ""} {entry.informations.fuel && <span className="text-danger mx-1">●</span>} {entry.informations.gearbox ?? ""}</h6>
                                                            {!preview && entry.informations?.din && <p className="m-0 mt-3 small"><span className="d-inline-block fw-bold" style={{ minWidth: "50%" }}>Puissance</span><span>{entry.informations?.din}ch</span></p>}
                                                            {!preview && entry.informations?.fiscal && <p className="m-0 small"><span className="d-inline-block fw-bold" style={{ minWidth: "50%" }}>Puissance fiscale</span><span>{entry.informations?.fiscal}cv</span></p>}
                                                            {!preview && entry.informations?.type && <p className="m-0 small"><span className="d-inline-block fw-bold" style={{ minWidth: "50%" }}>Type</span><span>{entry.informations?.type}</span></p>}
                                                            {!preview && entry.informations?.color && <p className="m-0 small"><span className="d-inline-block fw-bold" style={{ minWidth: "50%" }}>Couleur</span><span>{entry.informations?.color}</span></p>}
                                                            {!preview && entry.informations?.doors && <p className="m-0 small"><span className="d-inline-block fw-bold" style={{ minWidth: "50%" }}>Nombre de portes</span><span>{entry.informations?.doors}</span></p>}
                                                            {!preview && entry.informations?.sites && <p className="m-0 small"><span className="d-inline-block fw-bold" style={{ minWidth: "50%" }}>Nombre de places</span><span>{entry.informations?.sites}</span></p>}
                                                            <p className={`fs-5 ${entry.active === 0 ? 'text-secondary' : 'text-danger'} fw-bold text-end m-0 mt-4`.trim()}>{entry.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ")} € <sup className="text-secondary"><small><small>TTC</small></small></sup></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div key={`offer-details-${entry.id}`} style={showDetails === `offer-details-${entry.id}` ? { display: 'block', marginTop: '-0.25rem' } : { marginTop: '-0.25rem' }} className={`modal fade ${showDetails === `offer-details-${entry.id}` ? 'show' : 'hide'}`} id={`offer-${entry.id}`} tabIndex="-1" aria-labelledby={`label-offer-${entry.id}`} data-bs-backdrop="static" data-bs-keyboard="true" aria-hidden="true">
                                            <div className="modal-dialog modal-dialog-scrollable modal-lg text-body fg-body">
                                                <div className="modal-content">
                                                    <div className="modal-header">
                                                        <h1 className="modal-title fs-5" id={`label-offer-${entry.id}`}>{entry.active === 1 ? <span className="text-danger me-2">★</span> : <span className="text-secondary me-2">⧗</span>}<span className="text-secondary me-2 small fw-light"><small>#{entry.id}</small></span>{entry.name}</h1>
                                                        <button type="button" className="btn-close btn-sm small" aria-label="Fermer" onClick={() => hide()}></button>
                                                    </div>
                                                    <div className="modal-body row g-3 small p-0 m-0">
                                                        <div className="d-flex flex-column p-0 m-0" style={{ overflow: "hidden" }}>
                                                            {Array.isArray(entry.gallery) && entry.gallery.length > 0 && <div id={`offer-gallery-${entry.id}`} className="position-relative carousel slide p-0 px-4 m-0 bg-dark" data-bs-ride="carousel">
                                                                <div className="carousel-inner">
                                                                    {entry.gallery.map((url, i) =>
                                                                        <div key={`carousel-item-${i + 1}`} className={`carousel-item ${i === 0 && 'active'}`.trim()}>
                                                                            <Link target={"_blank"} to={url} rel="noopener noreferrer">
                                                                                <img src={`${url}/1000/500`} className={`d-block w-100 object-fit-contain`} style={{ height: "50vh" }} title="Cliquer pour agrandir" alt={`Photo ${i + 1}`} />
                                                                            </Link>
                                                                        </div>
                                                                    )}
                                                                </div>
                                                                <button className="carousel-control-prev" style={{ marginLeft: "-4%" }} type="button" data-bs-target={`#offer-gallery-${entry.id}`} data-bs-slide="prev">
                                                                    <span className="carousel-control-prev-icon" aria-hidden="true"></span>
                                                                    <span className="visually-hidden">Précédente</span>
                                                                </button>
                                                                <button className="carousel-control-next" style={{ marginRight: "-4%" }} type="button" data-bs-target={`#offer-gallery-${entry.id}`} data-bs-slide="next">
                                                                    <span className="carousel-control-next-icon" aria-hidden="true"></span>
                                                                    <span className="visually-hidden">Suivante</span>
                                                                </button>

                                                                <span className="position-absolute top-100 translate-middle badge rounded-pill bg-danger border border-white" style={{ left: "50%", transform: "translateX(-50%)" }}>
                                                                    {entry.gallery.length} photo{entry.gallery.length > 1 ? 's' : ''}
                                                                    <span className="visually-hidden">{entry.gallery.length} photo{entry.gallery.length > 1 ? 's' : ''}</span>
                                                                </span>
                                                            </div>
                                                            }

                                                            <div className="p-3 m-0">
                                                                <div className="d-flex flex-row align-items-center justify-content-between" style={{ gap: '0.5rem' }}>
                                                                    <div className="d-flex flex-row align-items-end" style={{ gap: '0.5rem' }}>
                                                                        {entry.informations?.brand && <div className="fs-3 text-uppercase">{entry.informations?.brand}</div>}
                                                                        {entry.informations?.model && <div className="fs-4 text-uppercase">{entry.informations?.model}</div>}
                                                                        {!entry.informations?.brand && !entry.informations?.model && <div className="fs-3 text-uppercase">{entry.name}</div>}
                                                                    </div>
                                                                    <div className="fs-3 fw-bold text-danger">
                                                                        {entry.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ")} € <sup className="text-secondary"><small><small>TTC</small></small></sup>
                                                                    </div>
                                                                </div>

                                                                <div className="mt-3 row p-2 mx-1 bg-body-tertiary">
                                                                    <div className="col-12 col-md-6 fw-bold"><span className="fw-light me-2">Mise en service :</span>{(new Date(parseInt(entry.release_date.substr(0, 4)), parseInt(entry.release_date.substr(5, 2)) - 1)).toLocaleDateString(process.env.LANG, { year: 'numeric', month: 'long' })}</div>
                                                                    <div className="col-12 col-md-6 fw-bold"><span className="fw-light me-2">Kilométrage :</span>{entry.mileage.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ")} km</div>

                                                                    {entry.informations?.din && <div className="col-12 col-md-6 fw-bold"><span className="fw-light me-2">Puissance :</span>{entry.informations?.din}ch</div>}
                                                                    {entry.informations?.fiscal && <div className="col-12 col-md-6 fw-bold"><span className="fw-light me-2">Puissance fiscale :</span>{entry.informations?.fiscal}cv</div>}

                                                                    {entry.informations?.type && <div className="col-12 col-md-6 fw-bold"><span className="fw-light me-2">Carrosserie :</span>{entry.informations?.type}</div>}
                                                                    {entry.informations?.color && <div className="col-12 col-md-6 fw-bold"><span className="fw-light me-2">Couleur :</span>{entry.informations?.color}</div>}

                                                                    {entry.informations?.doors && <div className="col-12 col-md-6 fw-bold"><span className="fw-light me-2">Nombre de portes :</span>{entry.informations?.doors}</div>}
                                                                    {entry.informations?.sites && <div className="col-12 col-md-6 fw-bold"><span className="fw-light me-2">Nombre de places :</span>{entry.informations?.sites}</div>}
                                                                </div>

                                                                <div className="card-text text-break lh-sm mt-3 p-2" dangerouslySetInnerHTML={{ __html: entry.description.replace(/(?:\r\n|\r|\n)/g, '<br />') }} />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="modal-footer">
                                                        <button type="button" className="btn btn-outline-dark btn-sm px-4" onClick={() => hide()}>Fermer</button>
                                                        <MessageButton className="btn btn-danger btn-sm px-4" text="Demande d'informations" subject={`[A#${entry.id} ${entry.name}] Demande d'informations`} subjectReadonly={true} message={`Bonjour,\r\n\r\nJe souhaiterai de plus amples informations au sujet de l'annonce proposée: ${entry.name}.\r\n\r\nCordialement.`} />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {showDetails !== '' && <div className="modal-backdrop fade show m-0"></div>}

                                    </Fragment>
                                })}
                            </div>


                        </>}
                    {preview && !loading && <p className="text-end fw-bolder small px-3 mt-4">❯ <Link to="/occasions" className="link-danger link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Toutes les occasions</Link></p>}
                    {!preview && offers.totalPage > 1 &&
                        <div className="btn-toolbar mt-5 mb-3" role="toolbar" aria-label="Toolbar with button groups">
                            <div className="btn-group btn-group-sm mx-auto" role="group" aria-label="First group">
                                <button type="button" className={`btn btn-outline-secondary px-4 text-danger ${(offers.page <= 1 || loading) && 'disabled'}`.trim()}>&laquo;</button>
                                {offers.start > 1 && <button type="button" className={`btn border-0 disabled`}>...</button>}
                                {Array(offers.paginator).fill(0).map((n, i) =>
                                    <button key={`page-${offers.start + i}`} type="button" className={`btn btn-outline-secondary px-3 ${loading && 'disabled'} ${offers.start + i === offers.page && 'text-danger fw-bold'}`.trim()} onClick={() => getOffers(offers.start + i)}>{offers.start + i}</button>
                                )}
                                {offers.end < offers.totalPage && <button type="button" className={`btn border-0 disabled`}>...</button>}
                                <button type="button" className={`btn btn-outline-secondary px-4 text-danger ${(offers.page >= offers.totalPage || loading) && 'disabled'}`.trim()}>&raquo;</button>
                            </div>
                        </div>
                    }
                </div >
            }
        </>
    );
}