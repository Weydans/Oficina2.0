<?php

/**
 * <b>Change</b>:
 * Classe do tipo helper, responsável por conversões genericas.
 * @author Weydans Campos de Barros, 18/04/2019.
 */
abstract class Change
{	

	/**
	 * <b>brlToTimestamp</b>:
	 * Converte uma string no formato datetime brl
	 * para um formato possivel de transformação para timestamp.
	 * @param string $dataHoraBrl Recebe a string no formato timestamp.
	 * @return string Retorna uma string preparada para conversão para timestamp.
	 */
	public static function brlToTimestamp($dataHoraBrl)
	{		
		$dataHora = explode(' ', $dataHoraBrl);
		$dataHoraChanged = null;

		if (is_array($dataHora)){
			$arrData = explode('/', $dataHora[0]);

			$aux = $arrData[1];
			$arrData[1] = $arrData[0];
			$arrData[0] = $aux;

			$dataHora[0] = implode('/', $arrData);
			$dataHoraChanged = implode(' ', $dataHora);
		}

		return $dataHoraChanged;
	}

}
