<?php

namespace Alura\Leilao\Tests\Domain;

use Alura\Leilao\Dao\Leilao as DaoLeilao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use DateTimeImmutable;
use Alura\Leilao\Service\EnviadorDeEmail;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EncerradorTest extends TestCase
{
    protected Encerrador $encerrador;
    /**@var MockObject */
    protected $enviadorEmail;
    protected array $leiloes;

    protected function setUp(): void
    {
        parent::setUp();
        $fiat147 = new Leilao(
            'Fiat 147 0km',
            new \DateTimeImmutable('8 days ago')
        );

        $variant = new Leilao(
            'Variant 1972 0Km',
            new \DateTimeImmutable('10 days ago')
        );

        $this->leiloes['fiat147'] = $fiat147;
        $this->leiloes['variant'] = $variant;

        $leilaoDao = $this->createMock(DaoLeilao::class);
        /* $leilaoDao = $this->getMockBuilder(DaoLeilao::class)
            ->setConstructorArgs([
                new \PDO('sqlite::memory:')
            ])->disableOriginalConstructor()->getMock(); */

        $leilaoDao->method('recuperarNaoFinalizados')
            ->willReturn([$this->leiloes['fiat147'], $this->leiloes['variant']]);


        $leilaoDao->method('recuperarFinalizados')
            ->willReturn([$this->leiloes['fiat147'], $this->leiloes['variant']]);

        $leilaoDao->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive(
                [$this->leiloes['fiat147']],
                [$this->leiloes['variant']]
            );



        /* $leilaoDao->expects($this->once())
            ->method('atualiza')
            ->with($this->leiloes['fiat147']); */
        $this->enviadorEmail = $this->createMock(EnviadorDeEmail::class);

        $this->encerrador = new Encerrador($leilaoDao, $this->enviadorEmail);
    }

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {

        $this->encerrador->encerra();

        //Assert
        $leiloes = [$this->leiloes['fiat147'], $this->leiloes['variant']];

        $this->assertCount(2, $leiloes);
        $this->assertTrue($leiloes[0]->estaFinalizado());
        $this->assertTrue($leiloes[1]->estaFinalizado());
    }

    public function testeDeveContinuarOProcessamentoAoEncontrarErroAoEnviarEmail()
    {
        $exception = new \DomainException("Erro ao enviar email");

        $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->willThrowException($exception);
        $this->encerrador->encerra();
    }

    public function testSoDeveEnviarLeilaoPorEmailAposFinalizado()
    {
        $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->willReturnCallback(function (Leilao $leilao) {
                $this->assertTrue($leilao->estaFinalizado());
            });

        /* $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->with('email'); */

        /* $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->with($this->greaterThan(1)); */
        $this->encerrador->encerra();
    }
}
