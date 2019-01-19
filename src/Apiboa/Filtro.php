<?php

namespace Api\Boa;

use Api\Boa\utils\Util;

class Filtro {

    public function json($res) {

        $Util  = new Util();
        $cpf    = $Util->corta($res,'CPF:','</tbody>');
        $cpfx   = explode('<br/>', $cpf);
        $cpf    = strip_tags(trim(rtrim($cpfx[0])));
        if(!stristr($cpf, '.')){
            $cpf    = strip_tags(trim(rtrim($cpfx[1])));
        }

        $cpf = trim(rtrim($cpf));
        $doc = $cpf;
        /* INICIO SCORE */
        $score_credito = $Util->corta($res, '<div align="center" class="subtitlexlarge" style="color:#CC0000; '.
            'font-family:Arial,Verdana,Helvetica,sans-serif; font-size:28px; font-weight:bold;">', '</div>');

        $score_credito = $Util->limpa_strings($score_credito);
        $score         = $Util->corta($res, '<table width="100%" height="66" border="0" cellpadding="3" cellspacing="1" class="tbScore">', '<!-- ## END: SCORE CREDITO ## -->');
        $score         = explode('<strong>', $score);

        if(count($score) > 1)
        {
            $classe_score         = $Util->limpa_strings($score[2]);
            $prob_legend          = $Util->limpa_strings($score[4]);
            $probabilidadeLegenda = explode('%', $prob_legend);
            $score_porcentagem    = $Util->limpa_strings($probabilidadeLegenda[0]) . '%';

            $score_legenda        = @$Util->limpa_strings($probabilidadeLegenda[1]);
            $score_legenda        = $Util->limpa_strings($score_legenda .' '. $score[5]);
            $score_legenda        = str_replace('   ', '', $score_legenda);
        }
        else
        {
            $score_credito = $Util->corta($res, '<table width="383" cellspacing="0" cellpadding="0" border="0" background="./boa/_img/img_bvs_sco_Score.png" height="65">', '<!-- ## END: SCORE CREDITO ## -->');

            $score         = explode('<strong>', $score_credito);

            $score_credito = explode('CLASSE DE SCORE', $Util->limpa_strings($score_credito));
            $score_credito = $score_credito[0];

            $classe_score         = $Util->limpa_strings($score[2]);
            $prob_legend          = $Util->limpa_strings($score[4]);
            $probabilidadeLegenda = explode('%', $prob_legend);
            $score_porcentagem    = $Util->limpa_strings($probabilidadeLegenda[0]) . '%';

            $score_legenda        = @$Util->limpa_strings($probabilidadeLegenda[1]);
            $score_legenda        = $Util->limpa_strings($score_legenda .' '. $score[5]);
            $score_legenda        = str_replace('   ', '', $score_legenda);
        }
        /* FIM SCORE */


        /* INICIO IDENTIFICACAO */
        $identificacao = $Util->corta($res, '<th colspan="4"><strong class="blue">IDENTIFICA', '</tbody');
        $identificacao = preg_split('/(<strong>|<br\/>|<br>)/', $Util->clean_string($identificacao));
        if(preg_replace('/\s/', '', strip_tags($identificacao[3])) != 'NomedaM&atilde;e')
        {
            array_splice($identificacao, 3, 0, "Nome da Mãe");
            array_splice($identificacao, 4, 0, "-");
        }

        $nome = $Util->limpa_strings($identificacao[2]);

        if(strlen($nome) < 3)
        {
            return 'nada encontrado';
        }

        $loc_ok = array();

        $nome_mae         = $Util->limpa_strings($identificacao[4]);
        $situacao_cpf     = $Util->limpa_strings($identificacao[8]);
        $data_atualizacao = $Util->limpa_strings($identificacao[10]);
        $Origem           = $Util->limpa_strings($identificacao[12]);
        $data_nascimento  = "-";
        $nacionalidade    = "-";
        $sexo             = "-";
        $civil            = "-";
        $dependentes      = "-";
        $escolaridade     = "-";

        if(!isset($identificacao[13]))
        {
            array_splice($identificacao, 13, 0, "Data de Nascimento");
            array_splice($identificacao, 14, 0, "-");
        }
        else
        {
            $data_nascimento = $Util->limpa_strings($identificacao[14]);
        }

        if(count($identificacao) > 15)
        {
            switch (preg_replace('/\s/', '', $Util->limpa_strings($identificacao[15])))
            {
                case 'Nacionalidade':
                    $nacionalidade = $Util->limpa_strings($identificacao[16]);
                    break;
                case 'Sexo':
                    $sexo = $Util->limpa_strings($identificacao[16]);
                    break;
                case 'EstadoCivil':
                    $civil = $Util->limpa_strings($identificacao[16]);
                    break;
                case 'Dependentes':
                    $dependentes = $Util->limpa_strings($identificacao[16]);
                    break;
                case 'GraudeInstru&ccedil;&atilde;o':
                    $escolaridade = $Util->limpa_strings($identificacao[16]);
                    break;
            }
        }

        if(count($identificacao) > 17)
        {
            switch (preg_replace('/\s/', '', $Util->limpa_strings($identificacao[17])))
            {
                case 'Sexo':
                    $sexo = $Util->limpa_strings($identificacao[18]);
                    break;
                case 'EstadoCivil':
                    $civil = $Util->limpa_strings($identificacao[18]);
                    break;
                case 'Dependentes':
                    $dependentes = $Util->limpa_strings($identificacao[18]);
                    break;
                case 'GraudeInstru&ccedil;&atilde;o':
                    $escolaridade = $Util->limpa_strings($identificacao[18]);
                    break;
            }
        }

        if(count($identificacao) > 19)
        {
            switch (preg_replace('/\s/', '', $Util->limpa_strings($identificacao[19])))
            {
                case 'EstadoCivil':
                    $civil = $Util->limpa_strings($identificacao[20]);
                    break;
                case 'Dependentes':
                    $dependentes = $Util->limpa_strings($identificacao[20]);
                    break;
                case 'GraudeInstru&ccedil;&atilde;o':
                    $escolaridade = $Util->limpa_strings($identificacao[20]);
                    break;
            }
        }

        if(count($identificacao) > 21)
        {
            switch (preg_replace('/\s/', '', $Util->limpa_strings($identificacao[21])))
            {
                case 'Dependentes':
                    $dependentes = $Util->limpa_strings($identificacao[22]);
                    break;
                case 'GraudeInstru&ccedil;&atilde;o':
                    $escolaridade = $Util->limpa_strings($identificacao[22]);
                    break;
            }
        }
        /* FIM IDENTIFICACAO */


        /* INICIO LOCALIZACAO */
        $localizacao = $Util->corta($res, '<th colspan="3"><strong class="blue">LOCALIZA', '</tbody>');
        $localizacao = preg_split('/(<strong>|<br\/>)/', $Util->clean_string($localizacao));
        $localizacao = $Util->clean_string($localizacao);

        if(count($localizacao) > 1)
        {
            if(stristr($localizacao[1], 'Endereço')){
                $endereco   = str_replace('Endereço', '', $Util->limpa_strings($localizacao[1]));
                $bairro     = str_replace('Bairro', '', $Util->limpa_strings($localizacao[2]));
                $cidade     = str_replace('Cidade', '', $Util->limpa_strings($localizacao[3]));
                $uf         = str_replace('UF', '', $Util->limpa_strings($localizacao[4]));
                $cep        = str_replace('CEP', '', $Util->limpa_strings($localizacao[5]));
                $tel        = array(str_replace('Telefone', '', $Util->limpa_strings($localizacao[6])));
            }
            else
            {
                $endereco   = $Util->limpa_strings($localizacao[2]);
                $bairro     = $Util->limpa_strings($localizacao[4]);
                $cidade     = $Util->limpa_strings($localizacao[6]);
                $uf         = $Util->limpa_strings($localizacao[8]);
                $cep        = $Util->limpa_strings($localizacao[10]);
                $tel        = Array();
            }

            if(isset($localizacao[11]))
            {
                $j = 12;
                $i = 0;
                while ($j < count($localizacao))
                {
                    $ver = $Util->limpa_strings($localizacao[$j]);
                    if(strlen($ver) > 5)
                    {
                        $tel[$i] = $ver;
                    }
                    $j+=2;
                    $i++;

                }
            }
        }
        else
        {
            $localizacao = 'Nada Consta';
        }


        /* FIM LOCALIZACAO */


        /* INICIO OUTRAS GRAFIAS */
        $grafias = $Util->corta($res, '<th colspan="5"><strong class="blue">OUTRAS GRAFIAS</strong></th>', '</tbody>');
        $grafias =  explode('<strong>Nome:</strong>', $Util->clean_string($grafias));

        if(count($grafias) > 1)
        {
            $arrGrafias = Array();
            for($i = 0; $i < count($grafias); $i++)
            {
                $arrGrafias[]   = $grafias[$i];
                $arrGrafias[$i] = preg_split("/(<td|:)/", $grafias[$i]);
                
                if(isset($arrGrafias[$i][8]) && preg_replace('/\s+/', '', $arrGrafias[$i][8]) == 'colspan="3"width="60%"><strong>Endere&ccedil;o')
                {
                    array_splice($arrGrafias[$i], 8, 0, "-");
                }

                if(isset($arrGrafias[$i][11]) && preg_replace('/\s+/', '', $arrGrafias[$i][11]) != 'colspan="2"width="40%"><strong>Bairro')
                {
                    array_splice($arrGrafias[$i], 12, 0, "-");
                }

                if(isset($arrGrafias[$i][17]) && preg_replace('/\s+/', '', $arrGrafias[$i][17]) != '><strong>CEP')
                {
                    array_splice($arrGrafias[$i], 18, 0, "-");
                }

                if(isset($arrGrafias[$i][19]) && preg_replace('/\s+/', '', $arrGrafias[$i][19]) == 'colspan="2"style="width')
                {
                    $arrGrafias[$i][20] = '-';
                    $arrGrafias[$i][21] = '-';
                    $arrGrafias[$i][22] = '-';
                }

                if(!isset($arrGrafias[$i][20]))
                {
                    $arrGrafias[$i][20] = '-';
                    $arrGrafias[$i][21] = '-';
                    $arrGrafias[$i][22] = '-';
                }

                if(preg_replace('/\s+/', '', $arrGrafias[$i][21]) == 'colspan="2">&nbsp;</td>')
                {
                    $arrGrafias[$i][21] = '-';
                }

                if(preg_replace('/\s+/', '', $arrGrafias[$i][22]) == ">&nbsp;</td></tr><trclass='white'>")
                {
                    $arrGrafias[$i][22] = '-';
                }
            }
        }
        /* FIM OUTRAS GRAFIAS */

        /* INICIO PARTICIPACAO EM EMPRESAS */
        $blocoParticipacao  = $Util->corta($res, '<th colspan="2"><strong class="blue">PARTICIPA', '</tbody>');
        $blocoParticipacao_novo = '<thead><tr><th colspan="2"><strong class="blue">PARTICIPA'.$blocoParticipacao . '</tbody>';
        $blocoParticipacao_novo = str_replace(array("\t", "\r", "\n", "  "), '', $blocoParticipacao_novo);
        $blocoParticipacao_novo = str_replace("javascript:chamadaEmpresarialGold('", '?cnpj=', $blocoParticipacao_novo);
        $blocoParticipacao_novo = str_replace("');", '', $blocoParticipacao_novo);

        /* INICIO DEBITOS */
        $debitos    = $Util->corta($res, '<th colspan="7"><strong class="blue">REGISTRO DE D', '</tbody>');
        $debitos    = '<thead><th colspan="7"><strong class="blue">REGISTRO DE D'. $debitos . '</tbody>';
        $debitos    = str_replace(array("\t", "\r", "\n", "  "), '', $debitos);
        /* FIM DEBITOS */

        /* INICIO CHEQUES SEM FUNDO */
        $semFundo   = $Util->corta($res, '<th colspan="4"><strong class="blue">CHEQUES SEM FU', '</tbody>');
        $semFundo   = '<thead><th colspan="4"><strong class="blue">CHEQUES SEM FU'. $semFundo . '</tbody>';
        $semFundo   = str_replace(array("\t", "\r", "\n", "  "), '', $semFundo);
        /* FIM CHEQUES SEM FUNDO */

        /* INICIO PROTESTOS */
        $blocoProtestos = $Util->corta($res, '<th colspan="4"><strong class="blue">PROTESTOS</strong></th>', '</tbody>');
        $blocoProtestos =  '<thead><th colspan="4"><strong class="blue">PROTESTOS</strong></th>' . $blocoProtestos . '</tbody>';
        $blocoProtestos     = str_replace(array("\t", "\r", "\n", "  "), '', $blocoProtestos);
        /* FIM PROTESTOS */

        /* INICIO RECUPERACOES, FALENCIAS E ACOES JUDICIAIS */
        $blocoRFAJ  = $Util->corta($res, '<th colspan="4"><strong class="blue">RECUPERA', '</tbody>');
        $blocoRFAJ  =  '<thead><th colspan="4"><strong class="blue">RECUPERA' . $blocoRFAJ . '</tbody>';
        $blocoRFAJ  = str_replace(array("\t", "\r", "\n", "  "), '', $blocoRFAJ);

        /* ACOES CIVEIS */
        $blocoAC    = $Util->corta($res, 'VEIS</strong></th>', '</tbody>');
        $blocoAC    = '<thead><th colspan="5"><strong class="blue">A&Ccedil;&Otilde;ES C&Iacute;VEIS</strong></th>' . $blocoAC . '</tbody>';
        $blocoAC    = str_replace(array("\t", "\r", "\n", "  "), '', $blocoAC);
        /* FIM ACOES CIVEIS */

        /* INICIO OUTRAS INFORMACOES */
        $blocoOI    = $Util->corta($res, '<th><strong class="blue">OUTRAS INFORMA', '</tbody>');
        $blocoOI    = '<thead><th><strong class="blue">OUTRAS INFORMA' . $blocoOI . '</tbody>';
        $blocoOI    = str_replace(array("\t", "\r", "\n", "  "), '', $blocoOI);
        /* FIM OUTRAS INFORMACOES */

        $resultado = array(
            'identificacao' => array(
                'doc'               => $Util->limpa_strings($cpf),
                'nome'              => $Util->limpa_strings($nome),
                'nome_mae'          => $Util->limpa_strings($nome_mae),
                'data_nascimento'   => $Util->limpa_strings($data_nascimento),
                'dependentes'       => $Util->limpa_strings($dependentes),
                'nacionalidade'     => $Util->limpa_strings($nacionalidade),
                'sexo'              => $Util->limpa_strings($sexo),
                'estado_civil'      => $Util->limpa_strings($civil),
                'escolaridade'      => $Util->limpa_strings($escolaridade),
                'situacao_cpf'      => $Util->limpa_strings($situacao_cpf),
                'data_atualizacao'  => $Util->limpa_strings($data_atualizacao),
                'origem'            => @$Util->limpa_strings($origem)
            )
        );
    

        if(count($localizacao) > 1)
        {
            $loc_ok[] = array(
                'logradouro' => $Util->limpa_strings($endereco),
                'bairro'   => $Util->limpa_strings($bairro),
                'cidade'   => $Util->limpa_strings($cidade),
                'uf'       => $Util->limpa_strings($uf),
                'cep'      => $Util->limpa_strings($cep)
            );
        }

        $grafia_dados = array();

        if(count($grafias) > 1)
        {
            for($i = 1; $i < count($arrGrafias); $i++)
            {
                if($arrGrafias[$i][20] == '-' || $arrGrafias[$i][20] != '-')
                {
                    $telefone = $Util->limpa_strings($arrGrafias[$i][20]);
                }
                elseif($arrGrafias[$i][20] != '-' && $arrGrafias[$i][21] != '-')
                {
                    $telefone = $Util->limpa_strings(strip_tags($arrGrafias[$i][20]) . ' | '. $arrGrafias[$i][21]);
                }
                else
                {
                    $telefone = $Util->limpa_strings($arrGrafias[$i][20] . ' | '. $arrGrafias[$i][21] . ' | '. $arrGrafias[$i][22]);
                }

                if($telefone != '-')
                {
                    $tel[] = $telefone;
                }

                $loc_ok[] = array(
                        'logradouro' => $Util->limpa_strings($arrGrafias[$i][10]),
                        'bairro'     => $Util->limpa_strings($arrGrafias[$i][12]),
                        'cidade'     => $Util->limpa_strings($arrGrafias[$i][14]),
                        'uf'         => $Util->limpa_strings($arrGrafias[$i][16]),
                        'cep'        => $Util->limpa_strings($arrGrafias[$i][18]),
                );
            }
        }
        else
        {
            $grafia_dados = 'Nada Consta';
        }

        $resultado['enderecos'] = $Util->unique_multidim_array($loc_ok,'logradouro');
        foreach($resultado['enderecos'] as $ends)
        {
            $dados_enderecos = array(
                'doc'           => $doc,
                'logradouro'    => $ends['logradouro'],
                'numero'        => '',
                'complemento'   => '',
                'bairro'        => $ends['bairro'],
                'cidade'        => $ends['cidade'],
                'uf'            => trim(rtrim($ends['uf'])),
                'cep'           => $ends['cep'],
                'obs'           => '',
                'data_cadastro' => date("Y-m-d H:i:s"),
                'status'        => 1
            );

            // salvar enderecos?

        }

        if(count($tel) > 0)
        {
            $tel = array_unique($tel);
            $resultado['telefones'] = $tel;
        }

        foreach($resultado['telefones'] as $tels)
        {
            $ddd = substr($tels, 0, 2);
            $numero = substr($tels, 2);
            if(strlen($ddd) > 1){
                $dados_tels = array(
                    'doc'           => $doc,
                    'ddd'           => $ddd,
                    'numero'        => $numero,
                    'portabilidade' => '',
                    'obs'           => '',
                    'data_cadastro' => date("Y-m-d H:i:s"),
                    'status'        => 1
                );

                //salvar telefones?
            }
        }

        $resultado['score'] = array(
            'pontuacao'     => $score_credito,
            'classe'        => $classe_score,
            'probabilidade' => $score_porcentagem,
            'legenda'       => $score_legenda
        );

        if(strlen($score_credito) > 0){
            $dados_score = array(
                'doc'           => $doc,
                'score'         => $score_credito,
                'classe'        => $classe_score,
                'probabilidade' => $score_porcentagem,
                'legenda'       => $score_legenda,
                'data_coleta'   => date("Y-m-d H:i:s"),
                'status'        => 1
            );

            //salvar score??
        }

        $resultado['protestos'] = urlencode($blocoProtestos);
        $dados_protestos = array(
            'doc'           => $doc,
            'data'          => $blocoProtestos,
            'data_coleta'   => date("Y-m-d H:i:s"),
            'status'        => 1
        );
        //salvar protestos?
        
        $resultado['cheque_sem_fundo'] = urlencode($semFundo);
        $dados_semFundo = array(
            'doc'           => $doc,
            'data'          => $semFundo,
            'data_coleta'   => date("Y-m-d H:i:s"),
            'status'        => 1
        );
        //salvar cheques?

        $resultado['recuperacao_falencia'] = urlencode($blocoRFAJ);
        $dados_rec_falen = array(
            'doc'           => $doc,
            'data'          => $blocoRFAJ,
            'data_coleta'   => date("Y-m-d H:i:s"),
            'status'        => 1
        );
        //salvar recuperacao e falencia?

        $resultado['acoes_civeis']         = urlencode($blocoAC);
        $dados_acoes_civeis = array(
            'doc'           => $doc,
            'data'          => $blocoAC,
            'data_coleta'   => date("Y-m-d H:i:s"),
            'status'        => 1
        );
        //salvar acoes civies..?

        $resultado['participacao_empresas'] = urlencode($blocoParticipacao_novo);
        $dados_part_empres = array(
            'doc'           => $doc,
            'data'          => $blocoParticipacao_novo,
            'data_coleta'   => date("Y-m-d H:i:s"),
            'status'        => 1
        );
        //salvar participacao em empresas...?

        $resultado['debitos'] = urlencode($debitos);
        $dados_debitos = array(
            'doc'           => $doc,
            'data'          => $debitos,
            'data_coleta'   => date("Y-m-d H:i:s"),
            'status'        => 1
        );
        //salvar debitos??

        $resultado['outras_infos'] = urlencode($blocoOI);
        $dados_outras_infos = array(
            'doc'           => $doc,
            'data'          => $blocoOI,
            'data_coleta'   => date("Y-m-d H:i:s"),
            'status'        => 1
        );
        //salvar outras infos?

        if (array_key_exists("score", $resultado))
        {
            $score            = $resultado['score'];
            $score            = urlencode("<br/><table class='table table-bordred table-striped'><tbody><tr class='blue'><td colspan='5' align='left'>Pontuaçao: <strong>{$score['pontuacao']}</strong><br/>Probabilidade de inadimplencia: <strong>{$score['probabilidade']}</strong><br/>Obs: <span>{$score['legenda']}</span></span></td></tr></tbody></table>");
            $protestos        = urlencode("<table class='table table-bordred table-striped'>{$resultado['protestos']}</table>");
            $cheque_sem_fundo = urlencode("<table class='table table-bordred table-striped'>{$resultado['cheque_sem_fundo']}</table>");
            $acoes_civeis     = urlencode("<table class='table table-bordred table-striped'>{$resultado['acoes_civeis']}</table>");
            $debitos          = urlencode("<table class='table table-bordred table-striped'>{$resultado['debitos']}</table>");
            $outras_infos     = urlencode("<table class='table table-bordred table-striped'>{$resultado['outras_infos']}</table>"); 
            $participacao_empresas = urlencode("<table class='table table-bordred table-striped'>{$resultado['participacao_empresas']}</table>"); 
            $recuperacao_falencia  = urlencode("<table class='table table-bordred table-striped'>{$resultado['recuperacao_falencia']}</table>");

            if(strlen($score) > 0)
            {


            }
            
            return $resultado;
        }
        else
        {
            return false;
        }
    }
}
