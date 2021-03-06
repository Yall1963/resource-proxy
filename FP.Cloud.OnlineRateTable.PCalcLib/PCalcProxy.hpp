#pragma once

#include "PCalcLib.hpp"
#include "IPCalcProxy.hpp"

BEGIN_PCALC_LIB_NAMESPACE

interface class ICalculationResultProcessor;
interface class IActionResultProcessor;
interface class IEnvironmentProcessor;
interface class IProductDescriptionMapper;
interface class IPCalcManager;
ref class PCalcFactory;

private ref class PCalcProxy : System::MarshalByRefObject, public IPCalcProxy
{
public:
	PCalcProxy();
	PCalcProxy(IPCalcManager^ manager, IActionResultProcessor^ actionResultProcessor, IEnvironmentProcessor^ environmentProcessor, ICalculationResultProcessor^ calculationResultProcessor, IProductDescriptionMapper^ mapper);

	~PCalcProxy();

	virtual Shared::PCalcResultInfo^ Start(Shared::EnvironmentInfo^ environment, Shared::WeightInfo^ weight);
	virtual Shared::PCalcResultInfo^ Calculate(Shared::EnvironmentInfo^ environment, Shared::ProductDescriptionInfo^ product, Shared::ActionResultInfo^ actionResult);
	virtual Shared::PCalcResultInfo^ Calculate(Shared::EnvironmentInfo^ environment, Shared::ProductDescriptionInfo^ product);
	virtual Shared::PCalcResultInfo^ Back(Shared::EnvironmentInfo^ environment, Shared::ProductDescriptionInfo^ product);
	virtual void Init(System::String^ amxPath, System::String^ tablePath);

protected:
	!PCalcProxy();

private:
	PCalcFactory^ m_Factory;
	IPCalcManager^ m_Manager;
	ICalculationResultProcessor^ m_CalculationResultProcessor;
	IActionResultProcessor^ m_ActionResultProcessor;
	IEnvironmentProcessor^ m_EnvironmentProcessor;
	IProductDescriptionMapper^ m_ProductDescriptionMapper;
};

END_PCALC_LIB_NAMESPACE




