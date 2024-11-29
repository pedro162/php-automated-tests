<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Service\EnviadorDeEmail;

class Encerrador
{
    public function __construct(protected LeilaoDao $dao, protected EnviadorDeEmail $enviadorDeEmail) {}

    public function encerra()
    {
        $leiloes = $this->dao->recuperarNaoFinalizados();

        foreach ($leiloes as $leilao) {

            if ($leilao->temMaisDeUmaSemana()) {
                try {
                    $leilao->finaliza();
                    $this->dao->atualiza($leilao);
                    $this->enviadorDeEmail->notificarTerminoLeilao($leilao);
                } catch (\DomainException $e) {
                    error_log($e->getMessage());
                }
            }
        }
    }
}
