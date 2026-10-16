export default function Index({ reservas }) {
    return (
        <div>
            <h1>Minhas Reservas</h1>

            {reservas.map((reserva) => (
                <div key={reserva.id}>
                    <p>{reserva.nome}</p>
                </div>
            ))}
        </div>
    );
}
