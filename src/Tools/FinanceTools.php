<?php

namespace TheShit\Finance\Tools;

use Prism\Prism\Tool;

/**
 * Registry of all available finance tools.
 *
 * Usage:
 *   $agent->withTools(FinanceTools::all());
 *   $agent->withTools([GetSpendingSummaryTool::make(), ...]);
 */
class FinanceTools
{
    /**
     * All core finance tools, ready to spread into any Prism agent.
     *
     * @return Tool[]
     */
    public static function all(): array
    {
        return [
            GetSpendingSummaryTool::make(),
            GetAccountBalancesTool::make(),
            GetCashflowTool::make(),
        ];
    }

    /**
     * Spending analysis only.
     *
     * @return Tool[]
     */
    public static function spending(): array
    {
        return [
            GetSpendingSummaryTool::make(),
            GetCashflowTool::make(),
        ];
    }
}
