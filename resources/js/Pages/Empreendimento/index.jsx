export default function Index({ empreendimentos }) {
  return (
    <div>
      {empreendimentos.map(empreendimento => (
        <div key={empreendimento.id}>
          <h2>{empreendimento.nome}</h2>

          {empreendimento.unidades.map(unidade => (
            <div key={unidade.id}>
              <span>Unidade {unidade.numero}</span>
              <span>{unidade.status}</span>
            </div>
          ))}
        </div>
      ))}
    </div>
  )
}
