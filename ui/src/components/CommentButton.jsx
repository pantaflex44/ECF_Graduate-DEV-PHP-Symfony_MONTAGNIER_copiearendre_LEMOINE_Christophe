import React, { useContext, useEffect, useState } from "react";
import axios from 'axios';

import Alert from "./Alert";
import StarRating from "./StarRating";

export default function CommentButton({ text, onPosted = null, ...props }) {
    const [id, setId] = useState('commentModal');
    const defaultForm = { name: '', rating: 2.5, comment: "", submitting: false, submitted: false, errors: null };
    const [form, setForm] = useState({ ...defaultForm });
    const [sendError, setSendError] = useState('');
    const [visible, setVisible] = useState(false);

    const floatToStr = (num, size = 2) => num.toFixed(Math.max(num.toString().split('.')[1]?.length, size) || size);

    function handleChange(name, value) {
        setForm(old => {
            return { ...old, [name]: value };
        });
        setForm(old => {
            return {
                ...old,
                errors: null,
                submitting: false,
                submitted: false
            }
        });
    }

    function cancel() {
        setSendError('');
        setForm({ ...defaultForm });
        setVisible(false);
    }

    function validateAndSubmit() {
        const nameIsValid = form.name.trim().length >= 2;

        const isValid = (nameIsValid);
        const errors = isValid ? null : {
            'name': !nameIsValid
        };

        setForm(old => {
            return {
                ...old,
                errors,
                submitting: isValid,
                submitted: false
            }
        });
    }

    function handleSubmit(e) {
        e.preventDefault();
        setSendError('');
        validateAndSubmit();
    }

    function finishSubmit() {
        const escape = (text) => text.replace(/(<([^>]+)>)/gi, "");

        const data = new FormData();
        data.set('name', escape(form.name).trim());
        data.set('comment', escape(form.comment).trim());
        data.set('rating', form.rating);

        axios.post(process.env.API_ENDPOINT + '/add_comment', data)
            .then((response) => {
                if (response.status !== 201) throw new Error(response.statusText);

                setForm(old => {
                    return {
                        ...old,
                        errors: null,
                        submitting: false,
                        submitted: true
                    }
                });

                if (onPosted) onPosted();
            })
            .catch((ex) => {
                const message = ex.response?.data?.message || ex.message;
                setSendError(message);

                setForm(old => {
                    return {
                        ...old,
                        errors: null,
                        submitting: false,
                        submitted: false
                    }
                });
            });
    }

    useEffect(() => {
        if (form.submitting && form.errors === null) {
            finishSubmit();
        }
    }, [form.submitting]);

    useEffect(() => setId(`commentModal_${(Math.random() + 1).toString(36).substring(7)}`), []);

    return (
        <>
            <button type="button" className="w-auto btn btn-link text-danger m-0 p-0" onClick={() => setVisible(true)} {...props}>{text}</button>

            <div className={`modal fade ${visible ? 'show' : 'hide'}`} style={visible ? { display: 'block' } : null} id={id} tabIndex="-1" aria-labelledby="commentModalLabel" data-bs-backdrop="static" data-bs-keyboard="true" aria-hidden="true">
                <div className="modal-dialog modal-dialog-centered text-body fg-body">
                    <div className="modal-content text-start">
                        <form onSubmit={handleSubmit}>
                            <div className="modal-header">
                                <h1 className="modal-title fs-5" id={`commentModalLabel-${id}`}>Donner son avis</h1>
                                <button type="button" className="btn-close btn-sm small" aria-label="Fermer" disabled={form.submitting} onClick={() => cancel()}></button>
                            </div>
                            <div className="modal-body row g-3">
                                <div className="col-md-6">
                                    <label htmlFor={`name-${id}`} className="form-label col-form-label-sm">Nom ou pseudonyme</label>
                                    <input type="text" disabled={form.submitting || form.submitted} className="form-control form-control-sm focus-ring-danger text-capitalize" id={`name-${id}`} placeholder=" " value={form.name} onChange={(e) => handleChange('name', e.target.value)} />
                                    {form.errors && form.errors.name &&
                                        <div id={`comment-name-error-${id}`} className="form-text small text-danger">
                                            <small>Nom / Pseudonyme absent ou incorrect!</small>
                                        </div>
                                    }
                                </div>
                                <div className="col-md-6">
                                    <label htmlFor={`rating-${id}`} className="form-label col-form-label-sm">Note<span className="fw-light small ms-2"><small>(<b>{floatToStr(form.rating, 1)}</b> / 5)</small></span></label>
                                    <StarRating initialValue={form.rating} onChange={(newValue) => handleChange('rating', newValue)} disabled={form.submitting || form.submitted} id={`rating-${id}`} />
                                </div>
                                <div className="col-12">
                                    <label htmlFor={`comment-${id}`} className="form-label col-form-label-sm">Commentaire<span className="ms-1 small fw-light">(facultatif)</span></label>
                                    <textarea disabled={form.submitting || form.submitted} className="form-control form-control-sm focus-ring-danger" id={`comment-${id}`} rows="7" placeholder=" " value={form.comment} onChange={(e) => handleChange('comment', e.target.value)} />
                                </div>
                                {sendError &&
                                    <div className="col-12">
                                        <Alert title="Impossible d'envoyer votre avis!" message={sendError} />
                                    </div>
                                }
                                {form.submitted &&
                                    <div className="col-12">
                                        <Alert title="Merci!" message="Votre avis nous a été transmis. Nous le validerons dans les plus brefs délais!" type="alert-success" />
                                    </div>
                                }
                            </div>
                            <div className="modal-footer">
                                {form.submitted
                                    ? <button type="button" className="btn btn-dark btn-sm px-4" data-bs-dismiss="modal" onClick={() => cancel()}>Fermer</button>
                                    : <>
                                        <button type="button" className="btn btn-outline-dark btn-sm px-4" data-bs-dismiss="modal" disabled={form.submitting || form.submitted} onClick={() => cancel()}>Annuler</button>
                                        <button type="submit" className="btn btn-dark btn-sm px-4" disabled={form.submitting || form.submitted}>
                                            {form.submitting && <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>}
                                            {form.submitting ? 'Envoie...' : 'Poster'}
                                        </button>
                                    </>
                                }
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            {visible && <div className="modal-backdrop fade show"></div>}
        </>
    );
}