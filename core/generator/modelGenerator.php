<?php

/**
* <b>modelGenerator</b>:
* Arquivo responsável pela geração de todas as models do sistema.
* Utiliza de interface gráfica e regra simples para geração de classes.*
* @author Weydans Campos de Barros, 01/03/2019.
*/

// DEPENDÊNCIAS NECESSÁRIAS PARA O FUNCIONAMENTO DO SISTEMA
require('../config.php');
require('../helper/Render.php');
require('../helper/Msg.php');

$data = false;
$msg = null;
$view = null;

$data = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if ($data){

	// Criação do diretório
	if(!file_exists('../../app/model') && !is_dir('../../app/model')){
		$res = mkdir('../../app/model');
	}

	if (!empty($data['tabela']) && !empty($data['campos'])){
		$className = ucwords($data['tabela']);

		if (!file_exists("../../app/model/{$className}.php")){

			$tabela = "'" . $data['tabela'] . "'";
			$campos = strip_tags(trim($data['campos']));

			$arrayCampos = explode(', ', $campos);

			for ($i = 0; $i < count($arrayCampos); $i++) { 
				if ($i !== count($arrayCampos) - 1){
					$arrayCampos[$i] = "'" . $arrayCampos[$i] . "',";
				}else {
					$arrayCampos[$i] = "'" . $arrayCampos[$i] . "'";
				}
			}

			$campos = implode("\r\n\t\t", $arrayCampos);

			$private = (
				"<?php\r\n\r\nclass {$className} extends Model\r\n{\r\n"
			);

			$static = (
				"\tprivate static \$table = {$tabela};\r\n\tprivate static \$columns = [\r\n\t\t{$campos}\r\n\t];\r\n\r\n"
			);

			$basicMethods = (
				"\tpublic function save(array \$data)\r\n\t{\r\n\t\treturn \$this->saveModel(self::\$table, self::\$columns, \$data);\r\n\t}\r\n\r\n\tpublic function update(array \$data)\r\n\t{\r\n\t\treturn \$this->updateModel(self::\$table, self::\$columns, \$data);\r\n\t}\r\n\r\n\tpublic function find(int \$id)\r\n\t{\r\n\t\treturn \$this->findModel(self::\$table, self::\$columns, \$id);\r\n\t}\r\n\r\n\tpublic function listAll()\r\n\t{\r\n\t\treturn \$this->listAllModel(self::\$table);\r\n\t}\r\n\r\n\tpublic function delete(int \$id)\r\n\t{\r\n\t\treturn \$this->deleteModel(self::\$table, self::\$columns, \$id);\r\n\t}\r\n\r\n\tpublic function nextId()\r\n\t{\r\n\t\treturn \$this->nextIdModel(self::\$table, self::\$columns);\r\n\t}\r\n\r\n}\r\n"
			);


			$content = $private . $static . $basicMethods;

			$arquivo = fopen("../../app/model/{$className}.php", 'w+');
			$res = fwrite($arquivo, $content);
			fclose($arquivo);

			if ($res){
				$msg = [
					'<div class="msg"></div>' => Msg::setMsg("Model <b>{$className}.php</b> constrído com sucesso!", ACCEPT)
				];

			} else {
				$msg = [
					'<div class="msg"></div>' => Msg::setMsg("Erro ao construir model <b>{$className}.php</b>.", ERROR)
				];
			}

		} else {
			$msg = [
				'<div class="msg"></div>' => Msg::setMsg("O arquivo <b>{$className}.php</b> já existe no sistema.", ERROR)
			];
		}// end if file_exists

	} else {
		$msg = ['<div class="msg"></div>' => Msg::setMsg('Preencha todos os campos do formulário.', ERROR)];
	}// end if campos preenchidos

} else {
	$msg = ['<div class="msg"></div>' => ''];
} // end if Post

$view = Render::show('./model-generator.html', $msg);

echo $view;