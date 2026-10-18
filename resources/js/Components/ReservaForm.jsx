import { useState } from 'react';

export default function ReservaForm({ unidade, onCancel, onSuccess }) {
    const [nome, setNome] = useState('');
    const [email, setEmail] = useState('');
    const [enviando, setEnviando] = useState(false);
    const [erro, setErro] = useState('');

    async function reservar(event) {
        event.preventDefault();
        if (enviando) return;
        setEnviando(true);
        setErro('');

        try {
            const response = await fetch('/reservas', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ unidade_id: unidade.id, cliente_nome: nome.trim(), cliente_email: email.trim() }),
            });

            if (response.status === 419) {
                throw new Error('Sua sessão expirou. Recarregue a página e tente novamente.');
            }
            const dados = await response.json();
            if (!response.ok) {
                throw new Error(response.status === 422
                    ? Object.values(dados.errors ?? {}).flat().join(' ') || 'Confira os dados informados.'
                    : 'Não foi possível concluir a reserva. Tente novamente.');
            }
            onSuccess(unidade);
        } catch (error) {
            setErro(error instanceof TypeError || error instanceof SyntaxError
                ? 'Não foi possível confirmar a reserva. Atualize a lista antes de tentar novamente.'
                : error.message);
        } finally {
            setEnviando(false);
        }
    }

    return (
        <section className="reserva-form" aria-labelledby="reserva-titulo">
            <h2 id="reserva-titulo">Reservar unidade {unidade.numero}</h2>
            <p>{unidade.empreendimento}</p>
            <form onSubmit={reservar} aria-busy={enviando}>
                <fieldset disabled={enviando}>
                    <legend>Dados do cliente</legend>
                    <label htmlFor="cliente-nome">Nome</label>
                    <input id="cliente-nome" name="cliente_nome" autoComplete="name" autoFocus
                        value={nome} onChange={(event) => setNome(event.target.value)} required maxLength={100} />
                    <label htmlFor="cliente-email">E-mail</label>
                    <input id="cliente-email" name="cliente_email" type="email" autoComplete="email"
                        value={email} onChange={(event) => setEmail(event.target.value)} required maxLength={100} />
                    <div className="reserva-actions">
                        <button type="submit">{enviando ? 'Reservando…' : 'Confirmar reserva'}</button>
                        <button type="button" onClick={onCancel}>Cancelar</button>
                    </div>
                </fieldset>
                {erro && <p role="alert" className="empreendimentos-error">{erro}</p>}
            </form>
        </section>
    );
}
