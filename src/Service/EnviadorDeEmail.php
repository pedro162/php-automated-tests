<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Leilao;

class EnviadorDeEmail
{
    public function notificarTerminoLeilao(Leilao $leilao)
    {
        $sucesso = mail('usuario@email.com', 'Leilão finalizado', "O leilão para {$leilao->recuperarDescricao()}");

        if (!$sucesso) {
            throw new \DomainException("Erro ao enviar email");
        }
    }
}
