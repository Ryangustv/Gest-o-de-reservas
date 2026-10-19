import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import ReservaForm from '../../Components/ReservaForm';

export default function Index({ empreendimento = [] }) {
    const [empreendimentos, setEmpreendimentos] = useState(empreendimento);
    const [carregando, setCarregando] = useState(false);
    const [erro, setErro] = useState('');
    const [selecionada, setSelecionada] = useState(null);
    const [sucesso, setSucesso] = useState('');

    function reservaConcluida(unidade) {
        setEmpreendimentos((lista) => lista.map((empreendimento) => ({
            ...empreendimento,
            unidades: empreendimento.unidades.map((item) => item.id === unidade.id ? { ...item, status: 'reservado' } : item),
        })));
        setSelecionada(null);
        setSucesso(`Reserva da unidade ${unidade.numero} realizada com sucesso.`);
    }

    async function atualizar() {
        setCarregando(true);
        setErro('');
        try {
            const response = await fetch('/empreendimentos?atualizar=1', {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            });
            if (!response.ok) throw new Error('Falha na consulta.');
            const dados = await response.json();
            if (!Array.isArray(dados.empreendimento)) throw new Error('Resposta inválida.');
            setEmpreendimentos(dados.empreendimento);
            setSelecionada(null);
        } catch {
            setErro('Não foi possível atualizar os dados. Tente novamente.');
        } finally {
            setCarregando(false);
        }
    }

    return (
        <main className="empreendimentos-page">
            <Head title="Empreendimentos" />
            <header className="empreendimentos-header">
                <div>
                    <h1>Empreendimentos</h1>
                    <p>Conheça os empreendimentos e suas unidades disponíveis.</p>
                </div>
                <Link className="reservas-link" href="/reservas">Visualizar reservas</Link>
                <button type="button" onClick={atualizar} disabled={carregando || Boolean(selecionada)}>
                    {carregando ? 'Atualizando…' : 'Atualizar lista'}
                </button>
            </header>
            {erro && <p role="alert" className="empreendimentos-error">{erro}</p>}
            {sucesso && <p role="status" className="empreendimentos-notice">{sucesso}</p>}
            {selecionada && (
                <ReservaForm key={selecionada.id} unidade={selecionada}
                    onCancel={() => setSelecionada(null)} onSuccess={reservaConcluida} />
            )}
            <section aria-label="Lista de empreendimentos" aria-busy={carregando} className="empreendimentos-list">
                {empreendimentos.length === 0 && <p>Nenhum empreendimento cadastrado.</p>}
                {empreendimentos.map((empreendimento) => {
                    const unidades = empreendimento.unidades.filter((unidade) => unidade.status === 'disponivel');
                    return (
                        <article key={empreendimento.id} className="empreendimento-card">
                            <h2>{empreendimento.nome}</h2>
                            <p className="empreendimento-city">{empreendimento.cidade}</p>
                            <p>{unidades.length} {unidades.length === 1 ? 'unidade disponível' : 'unidades disponíveis'}</p>
                            {unidades.length === 0 ? <p>Nenhuma unidade disponível no momento.</p> : (
                                <ul className="unidades-list">
                                    {unidades.map((unidade) => (
                                        <li key={unidade.id}>
                                            <span>Unidade {unidade.numero}</span>
                                            <button type="button" disabled={carregando || Boolean(selecionada)}
                                                aria-label={`Reservar unidade ${unidade.numero} de ${empreendimento.nome}`}
                                                onClick={() => {
                                                    setSucesso('');
                                                    setSelecionada({ ...unidade, empreendimento: empreendimento.nome });
                                                }}
                                            >Reservar</button>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </article>
                    );
                })}
            </section>
        </main>
    );
}
