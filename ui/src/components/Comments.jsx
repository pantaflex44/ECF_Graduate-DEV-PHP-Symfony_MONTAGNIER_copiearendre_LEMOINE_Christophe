import React, { useContext, useEffect, useState } from "react";
import axios from 'axios';

import { AuthContext } from "../providers/AuthProvider";

import Alert from "./Alert";
import CommentButton from "./CommentButton";

export default function Comments() {
    const auth = useContext(AuthContext);

    const [comments, setComments] = useState({ error: null, list: [] });
    const [totals, setTotals] = useState({ comments_count: 0, rating_average: 0, remarks_count: 0, need_approve: 0 });

    let mounted = false;

    const floatToStr = (num, size = 2) => num.toFixed(Math.max(num.toString().split('.')[1]?.length, size) || size);
    const totalRatingAverage = (list) => Math.round((list.reduce((total, obj) => obj.rating + total, 0) / list.length) / 0.5) * 0.5;
    const escapeComment = (text) => text.replace(/(<([^>]+)>)/gi, "").replaceAll("\r", "").replaceAll("\n", "<br />");
    const plural = (count, pluralText, normalText = "") => count > 1 ? pluralText : normalText;
    function timeAgo(input) {
        const date = (input instanceof Date) ? input : new Date(input);
        const formatter = new Intl.RelativeTimeFormat(process.env.LANG);
        const ranges = {
            years: 3600 * 24 * 365,
            months: 3600 * 24 * 30,
            weeks: 3600 * 24 * 7,
            days: 3600 * 24,
            hours: 3600,
            minutes: 60,
            seconds: 1
        };
        const secondsElapsed = (date.getTime() - Date.now()) / 1000;
        for (let key in ranges) {
            if (ranges[key] < Math.abs(secondsElapsed)) {
                const delta = secondsElapsed / ranges[key];
                return formatter.format(Math.round(delta), key);
            }
        }
    }

    const Stars = ({ id, count, size = 32, cssColor = "rgb(var(--bs-warning-rgb))", className = null }) => <div className={className}>
        {
            count > 0 && Array.from(Array(Math.floor(count)))
                .map((v, i) =>
                    <svg key={`star-${id}-${i + 1}`} className="mx-1" width={`${size}px`} height={`${size}px`} viewBox="0 0 21 21" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink" >
                        <g id="star" stroke="none" strokeWidth="1" fill="none" fillRule="evenodd">
                            <g transform="translate(-99.000000, -320.000000)" fill="#000000">
                                <g transform="translate(56.000000, 160.000000)">
                                    <path style={{ fill: cssColor }} d="M60.556381,172.206 C60.1080307,172.639 59.9043306,173.263 60.0093306,173.875 L60.6865811,177.791 C60.8976313,179.01 59.9211306,180 58.8133798,180 C58.5214796,180 58.2201294,179.931 57.9282291,179.779 L54.3844766,177.93 C54.1072764,177.786 53.8038262,177.714 53.499326,177.714 C53.1958758,177.714 52.8924256,177.786 52.6152254,177.93 L49.0714729,179.779 C48.7795727,179.931 48.4782224,180 48.1863222,180 C47.0785715,180 46.1020708,179.01 46.3131209,177.791 L46.9903714,173.875 C47.0953715,173.263 46.8916713,172.639 46.443321,172.206 L43.575769,169.433 C42.4480682,168.342 43.0707186,166.441 44.6289197,166.216 L48.5916225,165.645 C49.211123,165.556 49.7466233,165.17 50.0227735,164.613 L51.7951748,161.051 C52.143775,160.35 52.8220755,160 53.499326,160 C54.1776265,160 54.855927,160.35 55.2045272,161.051 L56.9769285,164.613 C57.2530787,165.17 57.7885791,165.556 58.4080795,165.645 L62.3707823,166.216 C63.9289834,166.441 64.5516338,168.342 63.423933,169.433 L60.556381,172.206 Z" id="star_favorite-[#1499]"></path>
                                </g>
                            </g>
                        </g>
                    </svg>
                )
        }
        {
            count > 0 && (count - Math.floor(count) > 0) && <svg key={`halfstar-${id}-half`} className="mx-1" width={`${size}px`} height={`${size}px`} viewBox="0 0 21 21" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink">
                <g id="halfstar" stroke="none" strokeWidth="1" fill="none" fillRule="evenodd">
                    <g transform="translate(-419.000000, -280.000000)" fill="#000000">
                        <g transform="translate(56.000000, 160.000000)">
                            <path style={{ fill: cssColor }} d="M374,120 L374,137.714 C374,137.714 373.571353,137.786 373.269101,137.93 L369.638788,139.779 C369.321149,139.931 368.99252,140 368.673782,140 C367.465876,140 366.399753,139.01 366.629464,137.791 L367.365858,133.876 C367.481263,133.264 367.254849,132.64 366.766851,132.206 L363.63223,129.433 C362.402341,128.342 363.066195,126.441 364.766496,126.217 L369.058465,125.645 C369.73331,125.556 370.258678,125.17 370.55983,124.614 L372.375536,121.051 C372.755824,120.35 372.900904,120 374,120" id="star_favorite_half-[#1501]"></path>
                        </g>
                    </g>
                </g>
            </svg>
        }
    </div>;

    function setNewComments(comments_list) {
        let list = [...comments_list];

        list.sort(function (a, b) {
            if (a.id > b.id) return -1;
            if (a.id < b.id) return 1;
            return 0;
        });

        list.sort(function (a, b) {
            if (a.approved < b.approved) return -1;
            if (a.approved > b.approved) return 1;
            return 0;
        });

        list = list.map(l => ({
            ...l,
            rating: parseFloat(l.rating),
            dt: (l.dt instanceof Date) ? l.dt : new Date(Date.parse(l.dt.replace(/-/g, '/')))
        }));

        setComments(old => {
            return {
                ...old,
                error: null,
                list
            }
        });

        const newTotalRemarks = list.filter(c => c.comment.trim() !== '').length;
        const newTotalRatings = totalRatingAverage(list);
        const newTotalComments = list.length;
        const newNeedApprove = list.filter(c => c.approved === 0).length;
        setTotals(t => ({
            ...t,
            comments_count: newTotalComments,
            remarks_count: newTotalRemarks,
            rating_average: newTotalRatings,
            need_approve: newNeedApprove
        }));
    }

    async function getComments() {
        const options = auth.jwt.token ? { headers: { Authorization: `Bearer ${auth.jwt.token}` } } : undefined;

        axios.get(process.env.API_ENDPOINT + (auth?.user?.role === "admin" || auth?.user?.role === "worker" ? '/comments' : '/approved_comments'), options)
            .then(response => {
                if (mounted && response.status === 200) {
                    if (!Array.isArray(response.data)) throw new Error(response.data);
                    setNewComments(response.data);
                }
            })
            .catch(ex => {
                console.error(ex);
                let message = ex.message;
                switch (ex.request?.status) {
                    case 500:
                        message = "Une erreur interne s'est produite!";
                        break;
                }
                const code = ex.request?.status ?? 500;
                setComments(old => {
                    return {
                        ...old,
                        error: `(#${code}) ${message}`,
                    }
                });
            })
            .finally(() => loading = false);
    }

    useEffect(() => {
        mounted = true;
        getComments();
        return (() => { mounted = false; })
    }, [auth.jwt.token]);

    const [editing, setEditing] = useState(false);
    const [askForDelete, setAskForDelete] = useState([]);

    function openDeleteConfirm(id) {
        if (!askForDelete.includes(id)) setAskForDelete(old => ([...old, id]));
    }

    function closeDeleteConfirm(id) {
        if (askForDelete.includes(id)) setAskForDelete(old => old.filter(v => v !== id));
    }

    async function commentAdded() {
        await getComments();
    }

    async function approveComment(id, state) {
        if (editing) return;
        setEditing(true);

        const data = new FormData();
        data.append('state', state === 1 ? 1 : 0);

        const options = auth.jwt.token ? { headers: { Authorization: `Bearer ${auth.jwt.token}` } } : undefined;

        axios.post(process.env.API_ENDPOINT + `/approve_comment/${Number(id).toString()}`, data, options)
            .then(response => {
                if (response.status === 200) {
                    const idComment = { ...comments.list.filter(c => c.id === id)[0], approved: state }
                    const newComments = [...comments.list.filter(c => c.id !== id), idComment];
                    setNewComments(newComments);
                }
            })
            .catch(ex => {
                console.error(ex);
            })
            .finally(() => {
                setEditing(false);
            })
    }

    async function deleteComment(id) {
        if (editing) return;
        setEditing(true);

        const options = auth.jwt.token ? { headers: { Authorization: `Bearer ${auth.jwt.token}` } } : undefined;

        axios.delete(process.env.API_ENDPOINT + `/delete_comment/${Number(id).toString()}`, options)
            .then(response => {
                if (response.status === 200) {
                    const newComments = [...comments.list.filter(c => c.id !== id)];
                    setNewComments(newComments);
                }
            })
            .catch(ex => {
                console.error(ex);
            })
            .finally(() => {
                setEditing(false);
                closeDeleteConfirm(id);
            })
    }

    return (
        <>
            {comments.error
                ? <Alert title="Impossible de récupérer la liste des commentaires!" message={comments.error} />
                : <>
                    <div className="mb-5">
                        <div className="row px-0 px-md-5">
                            {totals.comments_count > 0 &&
                                <div className="mt-2 mb-4 col-12 col-md-4 d-flex justify-content-center justify-content-md-start align-items-end">
                                    <div className="vstack text-center">
                                        <Stars id="average" className="mb-2 mt-3" count={totals.rating_average} size={32} />
                                        <div>
                                            <span className="display-1 fw-bold me-1">{floatToStr(totals.rating_average, 1)}</span><span className="display-6">/ 5</span>
                                        </div>
                                        <div className="small text-secondary"><small>basée sur {totals.comments_count} note{plural(totals.comments_count, "s")}</small></div>
                                        <CommentButton text="Noter ou rédiger un avis" className="w-auto btn btn-link text-danger m-0 mt-5 p-0" onPosted={commentAdded} />
                                        {(auth?.user?.role === "admin" || auth?.user?.role === "worker") && totals.need_approve > 0 &&
                                            <div className="bg-warning-subtle mt-5 mx-5 px-5 py-2 rounded border border-warning">
                                                <div className="w-100 py-1 fw-bold text-body fs-3">⚠</div>
                                                <div className="w-100 py-1 fw-normal text-body small text-wrap">{plural(totals.need_approve, "Les", "Le")} {totals.need_approve} premier{plural(totals.need_approve, "s")} commentaire{plural(totals.need_approve, "s")} {plural(totals.need_approve, "ne sont", "n'est")} pas encore approuvé{plural(totals.need_approve, "s")}.</div>
                                            </div>
                                        }
                                    </div>
                                </div>
                            }
                            <div className="col-12 col-md-8 pt-3">
                                {totals.comments_count > 0 && <div className="w-100 py-1 fw-light"><small>{plural(totals.remarks_count, totals.remarks_count, "Un")} client{plural(totals.remarks_count, "s")} {plural(totals.remarks_count, "ont", "a")} laissé{plural(totals.remarks_count, "s")} {plural(totals.remarks_count, "leur", "son")} avis sur un total de {totals.comments_count} notation{plural(totals.comments_count, "s")}.</small></div>}
                                {totals.comments_count === 0 && <div className="w-100 py-1">
                                    <div className="hstack">
                                        <span className="w-100">Encore aucun avis déposé par notre clientèle!</span>
                                        <CommentButton text="Soyez le premier à nous donner votre impressions!" className="w-auto btn btn-link text-danger m-0 p-0" onPosted={commentAdded} />
                                    </div>
                                </div>}

                                {comments.list.map(comment =>
                                    <div key={`comment-${comment.id}`} className={`hstack align-items-stretch mt-4 border-start border-3 ${comment.rating >= 2.5 ? (comment.rating >= 4 ? 'border-success' : 'border-secondary') : (comment.rating <= 1 ? 'border-danger' : 'border-warning')}`.trim()}>
                                        <div style={{ minHeight: "80px" }} className={`me-auto w-100 p-0 m-0 ${comment.approved !== 0 ? `bg-body-tertiary` : 'bg-warning-subtle'}`}>
                                            <div style={{ marginInline: 0 }} className="row ps-3 pe-2 py-2">
                                                <div className="col fw-bold small text-danger p-0 m-0 lh-1 pt-1">{comment.name} <span className="text-body fw-normal">à donné {comment.comment.trim() !== '' ? 'son avis' : 'une note'}</span><br /><span className="small fw-normal text-body">{timeAgo(comment.dt)}</span></div>
                                                <div className="col p-0 m-0 text-end">
                                                    <div className="vstack">
                                                        <Stars id={`comment-${comment.id}`} className="col m-0 p-0" count={comment.rating} size={16} />
                                                        <div className="small fw-bold text-end m-0 p-0 pe-1">
                                                            <span className="me-1 fw-bold">{floatToStr(comment.rating, 1)}</span><span className="fw-normal small"><small>/ 5</small></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {comment.comment.trim() !== '' && <div className="m-0 p-0 py-2 ps-3 pe-2" dangerouslySetInnerHTML={{ __html: escapeComment(comment.comment) }} />}
                                        </div>

                                        {(auth?.user?.role === "admin" || auth?.user?.role === "worker") &&
                                            <>
                                                <div style={{ width: '48px' }} className="p-2 vstack gap-3">
                                                    {editing
                                                        ? <div >
                                                            <div className="spinner-border spinner-border-sm" role="status">
                                                                <span className="visually-hidden">Edition en cours...</span>
                                                            </div>
                                                        </div>
                                                        : <>
                                                            <div title="Supprimer" onClick={() => openDeleteConfirm(comment.id)} style={{ cursor: 'pointer' }}>
                                                                <svg width="20px" height="20px" viewBox="0 0 21 21" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink">
                                                                    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd">
                                                                        <g transform="translate(-179.000000, -360.000000)" fill="#000000">
                                                                            <g transform="translate(56.000000, 160.000000)">
                                                                                <path style={{ fill: 'rgb(var(--bs-danger-rgb))' }} d="M130.35,216 L132.45,216 L132.45,208 L130.35,208 L130.35,216 Z M134.55,216 L136.65,216 L136.65,208 L134.55,208 L134.55,216 Z M128.25,218 L138.75,218 L138.75,206 L128.25,206 L128.25,218 Z M130.35,204 L136.65,204 L136.65,202 L130.35,202 L130.35,204 Z M138.75,204 L138.75,200 L128.25,200 L128.25,204 L123,204 L123,206 L126.15,206 L126.15,220 L140.85,220 L140.85,206 L144,206 L144,204 L138.75,204 Z" />
                                                                            </g>
                                                                        </g>
                                                                    </g>
                                                                </svg>
                                                            </div>
                                                            {comment.approved === 0
                                                                ? <div title="Annuler l'approbation" onClick={() => approveComment(comment.id, 1)} style={{ cursor: 'pointer' }}>
                                                                    <svg
                                                                        width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink">
                                                                        <g>
                                                                            <path fill="none" d="M0 0h24v24H0z" />
                                                                            <path style={{ fill: 'rgb(var(--bs-success-rgb))' }} d="M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm1 2v14h14V5H5zm6.003 11L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z" />
                                                                        </g>
                                                                    </svg>
                                                                </div>
                                                                : <div title="Approuver" onClick={() => approveComment(comment.id, 0)} style={{ cursor: 'pointer' }}>
                                                                    <svg
                                                                        width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink">
                                                                        <g>
                                                                            <path fill="none" d="M0 0h24v24H0z" />
                                                                            <path style={{ fill: 'rgb(var(--bs-warning-rgb))' }} d="M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm1 2v14h14V5H5z" />
                                                                        </g>
                                                                    </svg>
                                                                </div>
                                                            }
                                                        </>
                                                    }
                                                </div>
                                                <div className={`modal fade ${askForDelete.includes(comment.id) ? 'show' : 'hide'}`} style={askForDelete.includes(comment.id) ? { display: 'block' } : null} id={`ask-for-delete-comment-modal-${comment.id}`} tabIndex="-1" aria-labelledby={`delete-comment-modal-title--${comment.id}`} data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden={!askForDelete.includes(comment.id)}>
                                                    <div className="modal-dialog modal-dialog-centered modal-md text-body fg-body">
                                                        <div className="modal-content">
                                                            <div className="modal-header">
                                                                <h1 className="modal-title fs-5" id={`delete-comment-modal-title--${comment.id}`}>Supprimer un commentaire.</h1>
                                                                <button type="button" className="btn-close" aria-label="Fermer" onClick={() => closeDeleteConfirm(comment.id)}></button>
                                                            </div>
                                                            <div className="modal-body">
                                                                <p className="small">Voulez-vous supprimer le commentaire de {comment.name} ?</p>
                                                            </div>
                                                            <div className="modal-footer">
                                                                <button type="button" className="btn btn-outline-dark btn-sm px-4" onClick={() => closeDeleteConfirm(comment.id)}>Non</button>
                                                                <button type="button" className="btn btn-dark btn-sm px-4" onClick={() => { deleteComment(comment.id); }}>Oui</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {(askForDelete.includes(comment.id)) && <div className="modal-backdrop fade show"></div>}
                                            </>
                                        }

                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </>
            }
        </>
    );
}