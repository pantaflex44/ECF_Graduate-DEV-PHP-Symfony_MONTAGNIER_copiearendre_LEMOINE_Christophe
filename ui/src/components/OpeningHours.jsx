import React, { useContext, useEffect, useState } from "react";
import axios from 'axios';

import { AuthContext } from "../providers/AuthProvider";

import Alert from "./Alert";
import PeriodSelector from "./PeriodSelector";

export default function OpeningHours() {
    const auth = useContext(AuthContext);

    let mounted = false;

    const now = new Date();
    const theDay = now.getDay() === 0 ? 7 : now.getDay();
    const [hours, setHours] = useState({ error: null, list: {} })

    async function getOpeningHours() {
        axios.get(process.env.API_ENDPOINT + '/openings')
            .then(response => {
                if (mounted && response.status === 200) {
                    if (!Array.isArray(response.data)) throw new Error(response.data);

                    let days = [];
                    response.data.forEach(day => {
                        days[day.dayOfWeek] = day.hours.map(hs => {
                            return { 'open': hs.open, 'close': hs.close, 'id': hs.id };
                        });
                    });
                    setHours(old => {
                        return {
                            ...old,
                            error: null,
                            list: {
                                lun: days[1],
                                mar: days[2],
                                mer: days[3],
                                jeu: days[4],
                                ven: days[5],
                                sam: days[6],
                                dim: days[0],
                            }
                        }
                    });
                }
            })
            .catch(ex => {
                let message = ex.message;
                switch (ex?.request?.status) {
                    case 500:
                        message = "Une erreur interne s'est produite!";
                        break;
                }
                setHours(old => { return { ...old, error: `(#${ex?.request?.status || 0}) ${message}`, list: {} } });
            })
    }

    useEffect(() => {
        mounted = true;
        getOpeningHours();
        return (() => { mounted = false; })
    }, []);

    const [editing, setEditing] = useState(false);
    const [updateModalOpened, setUpdateModalOpened] = useState(false);

    function openUpdateModal() {
        setUpdateModalOpened(true);
    }

    function closeUpdateModal(id) {
        setUpdateModalOpened(false);
    }

    return (
        <>
            {hours.error
                ? <Alert title="Impossible de récupérer les horaires d'ouverture!" message={hours.error} />
                : <>

                    {Object.entries(hours.list).map((entry, i) => {
                        const [key, value] = entry;
                        const isDay = (theDay === i + 1 ? 'text-danger fw-bolder' : '').trim();
                        const row = (content) => <div key={`${key}`} className={`row small text-uppercase ${isDay} ps-1`}>{content}</div>
                        const dn = <div key={`${key}_details`} className="col fw-bolder small">{key.toUpperCase()}.</div>;
                        if (value.length === 0) return row(<>{dn}<div key={`${key}_closed`} className="col text-capitalize small">Fermé</div></>);
                        else {
                            o = value.map((v, i) => {
                                return <div key={`${key}_${i}`} className="col text-lowercase small">{`${v.open} - ${v.close}`}</div>
                            });
                            return row(<>{dn}{o}</>);
                        }
                    })}

                    {(auth?.user?.role === "admin" || auth?.user?.role === "worker") &&
                        <div className="mt-3 hstack gap-2 justify-content-center">
                            <div title="Modifier" onClick={() => openUpdateModal()} style={{ cursor: 'pointer' }} className="hstack gap-2 align-items-center bg-body rounded px-2 py-1">
                                <svg width="20px" height="20px" viewBox="0 0 21 21" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink">
                                    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd">
                                        <g transform="translate(-59.000000, -400.000000)" fill="#000000">
                                            <g transform="translate(56.000000, 160.000000)">
                                                <path style={{ fill: 'rgb(var(--bs-info-rgb))' }} d="M3,260 L24,260 L24,258.010742 L3,258.010742 L3,260 Z M13.3341,254.032226 L9.3,254.032226 L9.3,249.950269 L19.63095,240 L24,244.115775 L13.3341,254.032226 Z" />
                                            </g>
                                        </g>
                                    </g>
                                </svg>
                                <span className="small"><small>Modifier</small></span>
                            </div>

                        </div>
                    }

                    <div className={`modal fade ${updateModalOpened ? 'show' : 'hide'}`} style={updateModalOpened ? { display: 'block' } : null} id="update-open-hours" tabIndex="-1" aria-labelledby="update-open-hours-title" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden={!updateModalOpened}>
                        <div className="modal-dialog modal-dialog-centered modal-md text-body fg-body" style={{ '--bs-modal-width': '359px' }}>
                            <div className="modal-content">
                                <div className="modal-header">
                                    <h1 className="modal-title fs-5" id="update-open-hours-title">Modifier les plages d'ouvertures.</h1>
                                    <button type="button" className="btn-close" aria-label="Fermer" onClick={() => closeUpdateModal()}></button>
                                </div>
                                <div className="modal-body small">

                                    {Object.entries(hours.list).map((entry, i) => {
                                        const [key, value] = entry;
                                        const dayOfWeek = i + 1 === 7 ? 0 : i + 1;
                                        return <div key={`${key}`} className="hstack gap-4 py-2 align-items-start border-bottom border-1 border-light-subtle py-3">
                                            <span className="text-uppercase fw-bold" style={{ width: "40px" }}>{key}</span>
                                            <div className="vstack gap-2">
                                                {value.map((v, j) => {
                                                    return <PeriodSelector key={`${key}-${v.id}`} id={v.id} dayOkWeek={dayOfWeek} open={v.open} close={v.close} />
                                                })}
                                            </div>
                                        </div>;
                                    })}

                                    <div className="mt-4 text-secondary">Ajoutez, supprimez ou modifiez les plages horaires d'ouverture du garage. N'oubliez pas d'enregistrer chaque modifications pour qu'elles soient prises en compte!</div>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-outline-dark btn-sm px-4" onClick={() => closeUpdateModal()}>Fermer</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {(updateModalOpened) && <div className="modal-backdrop fade show"></div>}

                </>
            }
        </>
    );
}