#pragma once

#include "PCalcLib.hpp"

BEGIN_PCALC_LIB_NAMESPACE

ref class PCalcFactory;

//public interface class ICalculationResultProcessor
//{
//	Shared::PCalcResultInfo^ Handle();
//};

private ref class CalculationResultProcessor abstract
{
public:
	virtual Shared::PCalcResultInfo^ Handle();

protected:
	CalculationResultProcessor(PCalcFactory^ factory);
	virtual void SetDescription(Shared::PCalcResultInfo^ resultInfo) abstract;

	property PCalcFactory^ Factory { PCalcFactory^ get() { return m_Factory; } }

private:
	PCalcFactory^ m_Factory;
};


END_PCALC_LIB_NAMESPACE