<?php
/**
 * Autores: Lucas Santos Dalmaso e André Santoro
 * Email's: lucassdalmaso25@gmail.com e asbnbm00@gmail.com
 */
require_once 'CodeGenerator.php';
require_once 'LexicalAnalysis.php';
require_once 'Parser.php';
require_once 'SemanticAnalysis.php';
require_once 'SymbolTable.php';

class Compiler {
    public static function main() {
        $counter = 1;
        $isPrint = true;

        echo '<pre>';
        echo "=== Início da análise léxica ===\n";

        $lexical = new LexicalAnalysis();
        $arquivo = 'codigo.txt';

        # Análise Léxica
        if ($lexical->parser($arquivo)) {
            if ($isPrint) {
                echo "\nTabela de Símbolos:\n";
                foreach ($lexical->getSymbolTable() as $key => $value) {
                    echo $value . " : " . $key . "\n";
                }
                
                echo "\nTokens Gerados:\n";
                foreach ($lexical->getTokens() as $token) {
                    echo $counter . ": " . $token . "\n";
                    $counter++;
                }
            }
            echo "Análise léxica concluída com sucesso.\n";
        } else {
            echo "Erro durante a análise léxica.\n";
            return;
        }

        echo "\n=== Início da análise sintática (Parsing) ===\n";
        $tokens = $lexical->getTokens();
        $parser = new Parser($tokens, $isPrint);

        try {
            $parser->parseProgram(); 
            echo "Parsing concluído com sucesso.\n";
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            return;
        }
        echo "=== Fim da análise sintática (Parsing) ===\n";

        echo "\n=== Início da análise semântica ===\n";
        $semanticAnalysis = new SemanticAnalysis($lexical, $isPrint);
        if ($semanticAnalysis->analyze()) {
            echo "Análise semântica concluída com sucesso.\n";
        } else {
            echo "Erro durante a análise semântica.\n";
            return;
        }
        echo "=== Fim da análise semântica ===\n";

        # Code Generation
        echo "\n=== Início da geração de código (SML) ===\n";
        $symbolTable = new SymbolTable();
        $symbolTable1 = $lexical->getSymbolTable();
        $invertedSymbolTable = array_flip($symbolTable1);
        
        $codeGenerator = new CodeGenerator($tokens, $symbolTable, $invertedSymbolTable);
        $smlCode = $codeGenerator->generateCode();

        $outputFile = fopen('binary.txt', 'w');

        echo "\nCódigo SML Gerado:\n";
        foreach ($smlCode as $instruction) {
            $sign = ($instruction >= 0) ? "+" : "-";
            $absInstruction = abs($instruction);
            $formattedInstruction = sprintf("%s%04d", $sign, $absInstruction);

            echo $formattedInstruction . "\n";

            fwrite($outputFile, $formattedInstruction . "\n");
        }
        fclose($outputFile);

        echo "=== Fim da geração de código (SML) ===\n";
    }
}

Compiler::main();
?>
