import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';

export default function Index({ reservas = [] }) {
    const [lista, setLista] = useState(reservas);
    const [cancelando, setCancelando] = useState(null);
    const [erro, setErro] = useState('');
    const [sucesso, setSucesso] = useState('');

    async function cancelar(reserva) {
        if (cancelando !== null || !window.confirm(`Cancelar a reserva de ${reserva.cliente_nome}?`)) return;
        setCancelando(reserva.id);
        setErro('');
        setSucesso('');
        try {
            const response = await fetch(`/reservas/${reserva.id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
            });
            if (response.status === 419) throw new Error('Sua sessão expirou. Recarregue a página.');
            if (response.status === 403) throw new Error('Você não tem permissão para cancelar esta reserva.');
            if (!response.ok) throw new Error('Não foi possível cancelar a reserva. Recarregue a lista e tente novamente.');
            setLista((atual) => atual.filter((item) => item.id !== reserva.id));
            setSucesso('Reserva cancelada com sucesso. A unidade está disponível novamente.');
        } catch (error) {
            setErro(error instanceof TypeError ? 'Não foi possível confirmar o cancelamento. Recarregue a página.' : error.message);
        } finally {
            setCancelando(null);
        }
    }

    return (
        <main className="empreendimentos-page">
            <Head title="Reservas" />
            <header className="empreendimentos-header">
                <h1>Reservas</h1>
                <Link className="reservas-link" href="/empreendimentos">Voltar aos empreendimentos</Link>
            </header>
            {erro && <p role="alert" className="empreendimentos-error">{erro}</p>}
            {sucesso && <p role="status" className="empreendimentos-notice">{sucesso}</p>}
            <section className="empreendimentos-list" aria-label="Lista de reservas" aria-busy={cancelando !== null}>
                {lista.length === 0 && <p>Nenhuma reserva cadastrada.</p>}
                {lista.map((reserva) => (
                    <article key={reserva.id} className="empreendimento-card">
                        <h2>{reserva.unidade.empreendimento ?? 'Empreendimento não informado'}</h2>
                        <p>Unidade {reserva.unidade.numero ?? 'não informada'}</p>
                        <p>Cliente: {reserva.cliente_nome}</p>
                        <p>E-mail: {reserva.cliente_email}</p>
                        <p>Data da reserva: {reserva.data_reserva?.slice(0, 10).split('-').reverse().join('/')}</p>
                        <button type="button" disabled={cancelando !== null} onClick={() => cancelar(reserva)}
                            aria-label={`Cancelar reserva de ${reserva.cliente_nome}, unidade ${reserva.unidade.numero}`}>
                            {cancelando === reserva.id ? 'Cancelando…' : 'Cancelar reserva'}
                        </button>
                    </article>
                ))}
            </section>
        </main>
    );
}
