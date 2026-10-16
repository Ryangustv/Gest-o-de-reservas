export default function Index({ Empreendimento }) {
    return (
        <div>
            {Empreendimento.map((empreendimento) => (
                <div key={empreendimento.id}>
                    <h2>{empreendimento.nome}</h2>

                    {empreendimento.unidades.map((unidade) => (
                        <div key={unidade.id}>
                            {unidade.numero} - {unidade.status}
                        </div>
                    ))}
                </div>
            ))}
        </div>
    );
}
